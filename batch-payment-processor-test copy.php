<?php
class BatchPaymentProcessorTest {
    public $test_products = [];
    public $test_orders = [];

    public function __construct() {
        if (!method_exists($this, 'create_test_products')) {
            WP_CLI::error('create_test_products method not defined');
        }
    }

    public function run_test() {
        WP_CLI::log('=== Starting Batch Payment Processor Test ===');

        try {
            $this->cleanup_existing_test_data();
            $this->create_test_products();
            $this->create_batch_pending_orders();
            $this->simulate_batch_payment_processing();
            $this->cleanup_test_data();
        } catch (Exception $e) {
            WP_CLI::error('Test failed: ' . $e->getMessage());
            $this->cleanup_test_data();
        }

        WP_CLI::success('=== Batch Payment Processor Test Completed ===');
    }

    public function create_test_products() {
        WP_CLI::log('Creating test products...');

        $product_names = [
            'Batch Test Product 1',
            'Batch Test Product 2',
            'Batch Test Product 3'
        ];

        foreach ($product_names as $name) {
            try {
                $product = new WC_Product_Simple();
                $product->set_name($name);
                $product->set_regular_price(rand(10, 100));
                $product_id = $product->save();

                if (!$product_id) {
                    throw new Exception("Failed to create product: $name");
                }

                $this->test_products[] = $product_id;
                WP_CLI::log("Created product: $name (ID: $product_id)");
            } catch (Exception $e) {
                WP_CLI::warning("Error creating product $name: " . $e->getMessage());
            }
        }
    }

    public function create_batch_pending_orders() {
        WP_CLI::log('Creating batch pending orders...');

        if (empty($this->test_products)) {
            throw new Exception('No test products available');
        }

        // Register status if needed
        if (!in_array('wc-batch-pending', array_keys(wc_get_order_statuses()))) {
            register_post_status('wc-batch-pending', [
                'label' => 'Batch Pending',
                'public' => true,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop('Batch Pending (%s)', 'Batch Pending (%s)')
            ]);
        }

        // Create test orders
        for ($i = 0; $i < 3; $i++) {
            try {
                $order = wc_create_order();
                
                // Add random product
                $product_id = $this->test_products[array_rand($this->test_products)];
                $product = wc_get_product($product_id);
                $order->add_product($product, 1);

                // Set complete billing details
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

                // Set billing address
                foreach ($billing_address as $key => $value) {
                    $order->{"set_billing_$key"}($value);
                }

                // Set shipping address (same as billing)
                foreach ($billing_address as $key => $value) {
                    $order->{"set_shipping_$key"}($value);
                }

                // Set payment method
                $order->set_payment_method('stripe');
                $order->set_payment_method_title('Credit Card (Stripe)');

                // Set customer data
                update_post_meta($order->get_id(), '_stripe_customer_info', [
                    'name' => $billing_address['first_name'] . ' ' . $billing_address['last_name'],
                    'email' => $billing_address['email'],
                    'address' => [
                        'line1' => $billing_address['address_1'],
                        'line2' => $billing_address['address_2'],
                        'city' => $billing_address['city'],
                        'state' => $billing_address['state'],
                        'postal_code' => $billing_address['postcode'],
                        'country' => $billing_address['country']
                    ]
                ]);

                // Calculate totals
                $order->calculate_totals();
                $order->save();
                
                // Set to batch-pending status
                $order->update_status('batch-pending');

                $this->test_orders[] = $order->get_id();
                WP_CLI::log("Created batch pending order #{$order->get_id()}");
            } catch (Exception $e) {
                WP_CLI::warning("Error creating order: " . $e->getMessage());
            }
        }
    }

    public function simulate_batch_payment_processing() {
        WP_CLI::log('Simulating batch payment processing...');

        $orders = array_filter(array_map('wc_get_order', $this->test_orders));
        WP_CLI::log('Processing ' . count($orders) . ' test orders');

        foreach ($orders as $order) {
            try {
                WP_CLI::log("Processing order #{$order->get_id()}");

                // Get Stripe gateway
                $available_gateways = WC()->payment_gateways->payment_gateways();
                if (!isset($available_gateways['stripe'])) {
                    throw new Exception('Stripe gateway not available');
                }
                
                $gateway = $available_gateways['stripe'];

                // Set test payment data
                $_POST['stripe_token'] = 'tok_visa';
                $_POST['payment_method'] = 'stripe';
                $_POST['wc-stripe-payment-token'] = 'new';

                // Add test card info
                $order->update_meta_data('_stripe_source_id', 'tok_visa');
                $order->update_meta_data('_stripe_intent_id', 'pi_' . uniqid());
                $order->update_meta_data('_stripe_customer_id', 'cus_' . uniqid());
                $order->save();

                // Process payment
                $result = $gateway->process_payment($order->get_id());

                if ($result['result'] === 'success') {
                    $order->payment_complete();
                    $order->add_order_note('Test payment processed successfully');
                    WP_CLI::success("Payment successful for order #{$order->get_id()}");
                } else {
                    throw new Exception(isset($result['messages']) ? $result['messages'] : 'Unknown error');
                }
            } catch (Exception $e) {
                WP_CLI::warning("Payment failed for order #{$order->get_id()}: " . $e->getMessage());
                $order->update_status('failed');
                $order->add_order_note('Test payment failed: ' . $e->getMessage());
            }
        }
    }

    public function cleanup_test_data() {
        WP_CLI::log('Cleaning up test data...');

        foreach ($this->test_products as $product_id) {
            wp_delete_post($product_id, true);
            WP_CLI::log("Deleted product #{$product_id}");
        }

        foreach ($this->test_orders as $order_id) {
            wp_delete_post($order_id, true);
            WP_CLI::log("Deleted order #{$order_id}");
        }
    }

    public function cleanup_existing_test_data() {
        WP_CLI::log('Cleaning up existing test data...');
        
        $existing_products = get_posts([
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'any',
            's' => 'Batch Test Product'
        ]);
        
        foreach ($existing_products as $product) {
            wp_delete_post($product->ID, true);
        }

        $existing_orders = get_posts([
            'post_type' => 'shop_order',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'meta_key' => '_billing_email',
            'meta_value' => '@example.com',
            'meta_compare' => 'LIKE'
        ]);
        
        foreach ($existing_orders as $order) {
            wp_delete_post($order->ID, true);
        }
    }
}

// Run the test
try {
    $test = new BatchPaymentProcessorTest();
    $test->run_test();
} catch (Exception $e) {
    WP_CLI::error($e->getMessage());
}