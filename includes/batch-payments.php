<?php
// includes/batch-payments.php

// Direct logging function to help debug
function direct_debug_log($message) {
    if (is_array($message)) {
        $message = print_r($message, true);
    }
    $log_file = WP_CONTENT_DIR . '/batch-debug.log';
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

// Log when the payment method is selected
function log_payment_method($gateways) {
    if (is_array($gateways)) {
        direct_debug_log('Available payment gateways: ' . implode(', ', array_keys($gateways)));
    }
    return $gateways;
}
add_filter('woocommerce_available_payment_gateways', 'log_payment_method', 1);

// Register our custom order status
function register_batch_pending_order_status() {
    register_post_status('wc-batch-pending', array(
        'label'                     => 'Batch Pending',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop('Batch Pending <span class="count">(%s)</span>',
            'Batch Pending <span class="count">(%s)</span>')
    ));
}
add_action('init', 'register_batch_pending_order_status');

// Intercept the payment early
function intercept_early_payment($order_id) {
    direct_debug_log('Processing order #' . $order_id);
    $order = wc_get_order($order_id);
    
    if (!$order) {
        direct_debug_log('No order found for ID: ' . $order_id);
        return;
    }
    
    direct_debug_log('Payment method: ' . $order->get_payment_method());
    
    if ($order->get_payment_method() === 'stripe') {
        direct_debug_log('Setting order to batch-pending');
        $order->update_status('batch-pending', 'Order queued for batch processing');
        WC()->cart->empty_cart();
        
        wc_add_notice(__('Your order has been received and will be processed in our next payment batch.', 'your-theme'), 'success');
        
        direct_debug_log('Order status updated to batch-pending');
        
        // Prevent further processing
        remove_all_actions('woocommerce_payment_complete');
        remove_all_actions('woocommerce_order_status_processing');
    }
}
add_action('woocommerce_new_order', 'intercept_early_payment', 1);

// Add our status to order statuses list
function add_batch_pending_to_order_statuses($order_statuses) {
    direct_debug_log('Adding batch-pending to statuses');
    $new_statuses = array();
    foreach ($order_statuses as $key => $status) {
        $new_statuses[$key] = $status;
        if ($key === 'wc-pending') {
            $new_statuses['wc-batch-pending'] = 'Batch Pending';
        }
    }
    return $new_statuses;
}
add_filter('wc_order_statuses', 'add_batch_pending_to_order_statuses');

// Block processing for batch-pending orders
function prevent_processing($gateways) {
    if (!is_array($gateways)) {
        return $gateways;
    }
    
    if (isset(WC()->session)) {
        $order_id = WC()->session->get('order_awaiting_payment');
        if ($order_id) {
            $order = wc_get_order($order_id);
            if ($order && $order->get_status() === 'batch-pending') {
                direct_debug_log('Blocking payment for batch-pending order #' . $order_id);
                return array();
            }
        }
    }
    return $gateways;
}
add_filter('woocommerce_available_payment_gateways', 'prevent_processing', 999);

// Intercept Stripe intent creation
add_filter('wc_stripe_payment_intent_params', function($params, $order) {
    if ($order->get_status() === 'batch-pending') {
        direct_debug_log('Preventing Stripe intent for batch-pending order #' . $order->get_id());
        throw new Exception('Order queued for batch processing');
    }
    return $params;
}, 10, 2);

// Schedule weekly processing
function schedule_batch_processing() {
    if (!wp_next_scheduled('process_batch_payments')) {
        wp_schedule_event(strtotime('next Monday'), 'weekly', 'process_batch_payments');
    }
}
add_action('init', 'schedule_batch_processing');

// Process batch payments
function process_batch_payments() {
    direct_debug_log('Starting batch payment processing');
    
    $orders = wc_get_orders(array(
        'status' => 'batch-pending',
        'limit'  => -1,
    ));
    
    if (empty($orders)) {
        direct_debug_log('No batch-pending orders found');
        return;
    }
    
    foreach ($orders as $order) {
        try {
            if (!class_exists('WC_Gateway_Stripe')) {
                direct_debug_log('Stripe gateway class not found');
                continue;
            }
            
            $gateway = new WC_Gateway_Stripe();
            $result = $gateway->process_payment($order->get_id());
            
            if ($result['result'] === 'success') {
                $order->add_order_note('Batch payment processed successfully.');
                direct_debug_log('Successfully processed payment for order #' . $order->get_id());
            } else {
                throw new Exception('Payment processing failed');
            }
        } catch (Exception $e) {
            $error_message = 'Batch payment error: ' . $e->getMessage();
            $order->add_order_note($error_message);
            direct_debug_log($error_message . ' for order #' . $order->get_id());
        }
    }
}
add_action('process_batch_payments', 'process_batch_payments');


// Add a settings section in WooCommerce settings
function add_batch_payment_settings($settings) {
    $settings[] = array(
        'title' => __('Batch Payment Settings', 'your-textdomain'),
        'type'  => 'title',
        'id'    => 'batch_payment_options',
    );

    $settings[] = array(
        'title'    => __('Batch Frequency', 'your-textdomain'),
        'desc'     => __('Select how often batch payments should be processed.', 'your-textdomain'),
        'id'       => 'batch_payment_frequency',
        'type'     => 'select',
        'options'  => array(
            'weekly'    => __('Weekly', 'your-textdomain'),
            'biweekly'  => __('Bi-Weekly', 'your-textdomain'),
            'monthly'   => __('Monthly', 'your-textdomain'),
        ),
        'default'  => 'weekly',
        'desc_tip' => true,
    );

    $settings[] = array(
        'type' => 'sectionend',
        'id'   => 'batch_payment_options',
    );

    return $settings;
}
add_filter('woocommerce_get_settings_general', 'add_batch_payment_settings');
// Reschedule batch payments based on selected frequency
function reschedule_batch_processing() {
    // Get the selected frequency
    $frequency = get_option('batch_payment_frequency', 'weekly');

    // Clear any existing schedules
    wp_clear_scheduled_hook('process_batch_payments');

    // Determine schedule time
    $start_time = strtotime('next Monday'); // Default: Weekly
    $interval = 'weekly';

    if ($frequency === 'biweekly') {
        $start_time = strtotime('next Monday +1 week');
        $interval = 'biweekly';
    } elseif ($frequency === 'monthly') {
        $start_time = strtotime('first day of next month');
        $interval = 'monthly';
    }

    // Schedule event
    if (!wp_next_scheduled('process_batch_payments')) {
        wp_schedule_event($start_time, $interval, 'process_batch_payments');
    }
}
add_action('admin_init', 'reschedule_batch_processing');

function custom_cron_schedules($schedules) {
    $schedules['biweekly'] = array(
        'interval' => 1209600, // 2 weeks
        'display'  => __('Every Two Weeks', 'your-textdomain'),
    );
    $schedules['monthly'] = array(
        'interval' => 2592000, // 30 days
        'display'  => __('Monthly', 'your-textdomain'),
    );
    return $schedules;
}
add_filter('cron_schedules', 'custom_cron_schedules');
