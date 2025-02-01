<?php
class BatchPaymentProcessorDiagnostic {
    public function run_diagnostic() {
        WP_CLI::log('=== Starting Batch Payment Processor Diagnostic ===');

        try {
            $this->check_woocommerce();
            $this->check_stripe();
            $this->check_order_statuses();
            $this->test_product_creation();
            $this->test_order_creation();
            $this->cleanup_test_data();
        } catch (Exception $e) {
            WP_CLI::error('Diagnostic failed: ' . $e->getMessage());
            $this->cleanup_test_data();
            return;
        }

        WP_CLI::success('=== Diagnostic Completed Successfully ===');
    }

    private function check_woocommerce() {
        WP_CLI::log('Checking WooCommerce...');
        
        if (!class_exists('WooCommerce')) {
            throw new Exception('WooCommerce is not active');
        }
        
        if (!function_exists('WC')) {
            throw new Exception('WC() function not available');
        }

        WP_CLI::success('WooCommerce check passed');
    }

    private function check_stripe() {
        WP_CLI::log('Checking Stripe Gateway...');
        
        if (!class_exists('WC_Gateway_Stripe')) {
            throw new Exception('Stripe Gateway not found');
        }

        $available_gateways = WC()->payment_gateways->payment_gateways();
        if (!isset($available_gateways['stripe'])) {
            throw new Exception('Stripe Gateway not available in WC gateways');
        }

        WP_CLI::success('Stripe Gateway check passed');
    }

    private function check_order_statuses() {
        WP_CLI::log('Checking Order Statuses...');
        
        // Register the status if it doesn't exist
        if (!in_array('wc-batch-pending', array_keys(wc_get_order_statuses()))) {
            WP_CLI::log('Registering batch-pending status...');
            
            register_post_status('wc-batch-pending', [
                'label' => 'Batch Pending',
                'public' => true,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop('Batch Pending (%s)', 'Batch Pending (%s)')
            ]);

            // Verify registration
            if (!in_array('wc-batch-pending', array_keys(wc_get_order_statuses()))) {
                throw new Exception('Failed to register batch-pending status');
            }
        }

        WP_CLI::success('Order status check passed');
    }

    private function test_product_creation() {
        WP_CLI::log('Testing Product Creation...');
        
        try {
            $product = new WC_Product_Simple();
            $product->set_name('Diagnostic Test Product');
            $product->set_regular_price(10.00);
            $product_id = $product->save();

            if (!$product_id) {
                throw new Exception('Failed to create test product');
            }

            $this->test_product_id = $product_id;
            WP_CLI::success('Product creation test passed');
        } catch (Exception $e) {
            throw new Exception('Product creation failed: ' . $e->getMessage());
        }
    }

    private function test_order_creation() {
        WP_CLI::log('Testing Order Creation...');

        try {
            $order = wc_create_order();
            
            if (!$order) {
                throw new Exception('Failed to create order object');
            }

            // Add the test product
            $product = wc_get_product($this->test_product_id);
            $order->add_product($product, 1);

            // Set test billing details
            $billing_address = [
                'first_name' => 'Test',
                'last_name'  => 'Customer',
                'email'      => 'test@example.com',
                'phone'      => '123-456-7890',
                'address_1'  => '123 Test St',
                'city'       => 'Test City',
                'state'      => 'CA',
                'postcode'   => '90210',
                'country'    => 'US'
            ];

            foreach ($billing_address as $key => $value) {
                $order->{"set_billing_$key"}($value);
            }

            $order->calculate_totals();
            $order->save();

            // Try setting the status
            $order->update_status('batch-pending');

            $this->test_order_id = $order->get_id();
            WP_CLI::success('Order creation test passed');
        } catch (Exception $e) {
            throw new Exception('Order creation failed: ' . $e->getMessage());
        }
    }

    private function cleanup_test_data() {
        WP_CLI::log('Cleaning up test data...');
        
        if (isset($this->test_product_id)) {
            wp_delete_post($this->test_product_id, true);
        }
        
        if (isset($this->test_order_id)) {
            wp_delete_post($this->test_order_id, true);
        }
        
        WP_CLI::success('Cleanup completed');
    }
}

// Run the diagnostic
try {
    $diagnostic = new BatchPaymentProcessorDiagnostic();
    $diagnostic->run_diagnostic();
} catch (Exception $e) {
    WP_CLI::error($e->getMessage());
}