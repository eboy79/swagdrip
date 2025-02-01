<?php
class BatchPaymentProcessorTest {
    public $test_products = [];
    public $test_orders   = [];
    private $logger;

    public function __construct() {
        if (!method_exists($this, 'create_test_products')) {
            WP_CLI::error('create_test_products method not defined');
        }
        // Initialize logger
        $this->logger = wc_get_logger();
    }

    public function run_test() {
        WP_CLI::log('=== Starting Batch Payment Processor Test ===');
        
        try {
            // Add validation for WooCommerce and Stripe
            if (!class_exists('WooCommerce')) {
                throw new Exception('WooCommerce is not active');
            }
            
            if (!class_exists('WC_Gateway_Stripe')) {
                throw new Exception('Stripe gateway is not active');
            }

            $this->cleanup_existing_test_data();
            $this->create_test_products();
            $this->create_batch_pending_orders();
            $this->simulate_batch_payment_processing();
            $this->cleanup_test_data();
        } catch (Exception $e) {
            $this->logger->error('Test failed: ' . $e->getMessage(), ['source' => 'batch-payment-test']);
            WP_CLI::error('Test failed: ' . $e->getMessage());
            $this->cleanup_test_data();
        }

        WP_CLI::success('=== Batch Payment Processor Test Completed ===');
    }

    public function create_batch_pending_orders() {
        WP_CLI::log('Creating batch pending orders...');
    
        if (empty($this->test_products)) {
            throw new Exception('No test products available');
        }
    
        // Verify batch-pending status exists
        if (!in_array('wc-batch-pending', array_keys(wc_get_order_statuses()))) {
            register_post_status('wc-batch-pending', [
                'label' => 'Batch Pending',
                'public' => true,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop('Batch Pending (%s)', 'Batch Pending (%s)')
            ]);
            
            // Add status to WooCommerce order statuses
            add_filter('wc_order_statuses', function($order_statuses) {
                $order_statuses['wc-batch-pending'] = 'Batch Pending';
                return $order_statuses;
            });
        }

        // Create test orders with error handling
        for ($i = 0; $i < 3; $i++) {
            try {
                $order = wc_create_order();
                if (!$order || is_wp_error($order)) {
                    throw new Exception('Failed to create order object');
                }
                
                // Add random product with validation
                $product_id = $this->test_products[array_rand($this->test_products)];
                $product = wc_get_product($product_id);
                if (!$product || !$product->exists()) {
                    throw new Exception("Invalid product ID: $product_id");
                }
                
                $order->add_product($product, 1);
    
                // Set billing/shipping with validation
                $billing_address = [
                    'first_name' => 'Batch Test',
                    'last_name'  => 'Customer ' . ($i + 1),
                    'email'      => "batchtest{$i}@example.com",
                    'phone'      => '123-456-7890',
                    'address_1'  => '123 Test St',
                    'address_2'  => 'Apt 4B',
                    'city'       => 'Test City',
                    'state'      => 'CA',
                    'postcode'   => '90210',
                    'country'    => 'US'
                ];
    
                foreach ($billing_address as $key => $value) {
                    $setter = "set_billing_$key";
                    if (method_exists($order, $setter)) {
                        $order->$setter($value);
                    }
                }
    
                $shipping_fields = ['first_name', 'last_name', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country'];
                foreach ($shipping_fields as $key) {
                    if (isset($billing_address[$key])) {
                        $setter = "set_shipping_$key";
                        if (method_exists($order, $setter)) {
                            $order->$setter($billing_address[$key]);
                        }
                    }
                }
    
                // Set payment method with validation
                $available_gateways = WC()->payment_gateways->payment_gateways();
                if (!isset($available_gateways['stripe'])) {
                    throw new Exception('Stripe gateway not available');
                }
                
                $order->set_payment_method('stripe');
                $order->set_payment_method_title('Credit Card (Stripe)');
    
                // Set Stripe customer info
                $stripe_info = [
                    'name'    => $billing_address['first_name'] . ' ' . $billing_address['last_name'],
                    'email'   => $billing_address['email'],
                    'address' => [
                        'line1'       => $billing_address['address_1'],
                        'line2'       => $billing_address['address_2'],
                        'city'        => $billing_address['city'],
                        'state'       => $billing_address['state'],
                        'postal_code' => $billing_address['postcode'],
                        'country'     => $billing_address['country']
                    ]
                ];
                
                $order->update_meta_data('_stripe_customer_info', $stripe_info);
                
                // Calculate totals and save
                $order->calculate_totals();
                if (!$order->save()) {
                    throw new Exception("Failed to save order");
                }
                
                // Verify status update
                $order->update_status('batch-pending');
                if ($order->get_status() !== 'batch-pending') {
                    throw new Exception("Failed to update order status to batch-pending");
                }
    
                $this->test_orders[] = $order->get_id();
                $this->logger->info("Created batch pending order #{$order->get_id()}", ['source' => 'batch-payment-test']);
                WP_CLI::log("Created batch pending order #{$order->get_id()}");
            } catch (Exception $e) {
                $this->logger->error("Error creating order: " . $e->getMessage(), ['source' => 'batch-payment-test']);
                WP_CLI::warning("Error creating order: " . $e->getMessage());
            }
        }
    }

    public function simulate_batch_payment_processing() {
        WP_CLI::log('Simulating batch payment processing...');

        $orders = array_filter(array_map('wc_get_order', $this->test_orders));
        if (empty($orders)) {
            throw new Exception('No valid orders found for processing');
        }
        
        WP_CLI::log('Processing ' . count($orders) . ' test orders');

        foreach ($orders as $order) {
            try {
                WP_CLI::log("Processing order #{$order->get_id()}");

                $available_gateways = WC()->payment_gateways->payment_gateways();
                if (!isset($available_gateways['stripe'])) {
                    throw new Exception('Stripe gateway not available');
                }
                
                $gateway = $available_gateways['stripe'];

                // Set test payment data
                $_POST['stripe_token'] = 'tok_visa';
                $_POST['payment_method'] = 'stripe';
                $_POST['wc-stripe-payment-token'] = 'new';

                // Update test card info
                $order->update_meta_data('_stripe_source_id', 'tok_visa');
                $order->update_meta_data('_stripe_intent_id', 'pi_' . uniqid());
                $order->update_meta_data('_stripe_customer_id', 'cus_' . uniqid());
                
                if (!$order->save()) {
                    throw new Exception("Failed to save Stripe metadata");
                }

                // Process payment with validation
                $result = $gateway->process_payment($order->get_id());
                if (!is_array($result)) {
                    throw new Exception('Invalid payment processing result');
                }

                if ($result['result'] === 'success') {
                    $order->payment_complete();
                    $order->add_order_note('Test payment processed successfully');
                    $this->logger->info("Payment successful for order #{$order->get_id()}", ['source' => 'batch-payment-test']);
                    WP_CLI::success("Payment successful for order #{$order->get_id()}");
                } else {
                    throw new Exception(isset($result['messages']) ? $result['messages'] : 'Unknown error');
                }
            } catch (Exception $e) {
                $this->logger->error("Payment failed for order #{$order->get_id()}: " . $e->getMessage(), ['source' => 'batch-payment-test']);
                WP_CLI::warning("Payment failed for order #{$order->get_id()}: " . $e->getMessage());
                $order->update_status('failed');
                $order->add_order_note('Test payment failed: ' . $e->getMessage());
            }
        }
    }
}