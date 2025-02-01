
<?php
class BatchPaymentProcessorTest {
    public $test_products = [];
    public $test_orders = [];
    protected $stripe_settings = null;
    protected $test_card = null;
    protected $is_hpos_enabled = false;

    public function __construct() {
        // Check if HPOS is enabled
        $this->is_hpos_enabled = wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled();
        
        if ($this->is_hpos_enabled) {
            WP_CLI::log('HPOS is enabled - using custom order tables');
        }

        // Get Stripe settings
        $this->stripe_settings = get_option('woocommerce_stripe_settings', []);
        if (empty($this->stripe_settings)) {
            WP_CLI::warning('Stripe gateway not properly configured');
        }

        // Set test card token
        $this->test_card = [
            'token' => 'tok_visa',
            'source' => 'src_' . uniqid(),
            'customer' => 'cus_' . uniqid(),
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 12,
                'exp_year' => date('Y') + 1,
                'cvc' => '314'
            ]
        ];
    }

    public function create_batch_pending_orders() {
        WP_CLI::log('Creating batch pending orders...');

        if (empty($this->test_products)) {
            throw new Exception('No test products available');
        }

        // Register status if needed
        $this->register_batch_pending_status();

        // Create test orders
        for ($i = 0; $i < 3; $i++) {
            try {
                // Use HPOS-compatible order creation
                $order = $this->create_order();
                
                // Add random product
                $product_id = $this->test_products[array_rand($this->test_products)];
                $product = wc_get_product($product_id);
                $order->add_product($product, 1);

                // Set address data
                $address = $this->get_test_address($i);
                $this->set_order_addresses($order, $address);

                // Set payment method
                $order->set_payment_method('stripe');
                $order->set_payment_method_title('Credit Card (Stripe)');

                // Calculate totals
                $order->calculate_totals();
                
                // Initialize Stripe customer data
                $customer_data = $this->init_stripe_customer($order);

                // Set to batch-pending status - use HPOS compatible method
                $order->set_status('batch-pending');
                $order->save();
                
                WP_CLI::debug("Order #{$order->get_id()} Stripe data: " . print_r($customer_data, true));

                $this->test_orders[] = $order->get_id();
                WP_CLI::log("Created batch pending order #{$order->get_id()}");
            } catch (Exception $e) {
                WP_CLI::warning("Error creating order: " . $e->getMessage());
            }
        }
    }

    protected function create_order() {
        if ($this->is_hpos_enabled) {
            // Use HPOS-specific creation method
            return new WC_Order();
        } else {
            // Use traditional creation method
            return wc_create_order();
        }
    }

    protected function register_batch_pending_status() {
        $status_name = 'batch-pending';
        
        // Check if status exists in both HPOS and traditional tables
        if ($this->is_hpos_enabled) {
            // Register for HPOS
            if (!in_array($status_name, wc_get_order_statuses())) {
                register_post_status("wc-{$status_name}", [
                    'label' => 'Batch Pending',
                    'public' => true,
                    'show_in_admin_all_list' => true,
                    'show_in_admin_status_list' => true,
                    'label_count' => _n_noop('Batch Pending (%s)', 'Batch Pending (%s)')
                ]);

                // Register with WooCommerce
                add_filter('wc_order_statuses', function($statuses) use ($status_name) {
                    $statuses["wc-{$status_name}"] = 'Batch Pending';
                    return $statuses;
                });
            }
        } else {
            // Traditional registration
            if (!in_array("wc-{$status_name}", array_keys(wc_get_order_statuses()))) {
                register_post_status("wc-{$status_name}", [
                    'label' => 'Batch Pending',
                    'public' => true,
                    'show_in_admin_all_list' => true,
                    'show_in_admin_status_list' => true,
                    'label_count' => _n_noop('Batch Pending (%s)', 'Batch Pending (%s)')
                ]);
            }
        }
    }

    protected function get_test_address($index) {
        return [
            'first_name' => 'Batch Test',
            'last_name'  => 'Customer ' . ($index + 1),
            'company'    => 'Test Company',
            'address_1'  => '123 Test St',
            'address_2'  => 'Apt 4B',
            'city'       => 'Test City',
            'state'      => 'CA',
            'postcode'   => '90210',
            'country'    => 'US',
            'phone'      => '123-456-7890',
            'email'      => "batchtest{$index}@example.com"
        ];
    }

    protected function set_order_addresses($order, $address) {
        // Set billing address
        foreach ($address as $key => $value) {
            $setter = "set_billing_" . $key;
            if (method_exists($order, $setter)) {
                $order->$setter($value);
            }
        }

        // Set shipping address
        foreach ($address as $key => $value) {
            if ($key !== 'email' && $key !== 'phone') {
                $setter = "set_shipping_" . $key;
                if (method_exists($order, $setter)) {
                    $order->$setter($value);
                }
            }
        }
    }

    public function cleanup_existing_test_data() {
        WP_CLI::log('Cleaning up existing test data...');
        
        // Clean up products
        $existing_products = wc_get_products([
            'status' => 'any',
            'limit' => -1,
            'name' => 'Batch Test Product'
        ]);
        
        foreach ($existing_products as $product) {
            $product->delete(true);
        }

        // Clean up orders - HPOS compatible
        $query_args = [
            'status' => 'any',
            'limit' => -1,
            'meta_key' => '_billing_email',
            'meta_value' => '@example.com',
            'meta_compare' => 'LIKE',
            'return' => 'ids',
        ];

        $order_ids = wc_get_orders($query_args);
        
        foreach ($order_ids as $order_id) {
            wc_get_order($order_id)->delete(true);
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

                // Setup test payment data
                $_POST = [
                    'payment_method' => 'stripe',
                    'stripe_token' => $this->test_card['token'],
                    'wc-stripe-payment-token' => 'new',
                    'wc-stripe-new-payment-method' => 'true',
                    'stripe_source' => $this->test_card['token'],
                    'billing_email' => $order->get_billing_email(),
                    'stripe_customer' => $this->test_card['customer']
                ];

                // Ensure test mode is properly set
                $order->update_meta_data('_test_mode', 'yes');
                $order->update_meta_data('_stripe_test_mode', true);
                $order->save();

                WP_CLI::debug("Processing payment with token: {$this->test_card['token']}");

                // Process payment
                $result = $gateway->process_payment($order->get_id());
                
                WP_CLI::debug("Payment result: " . print_r($result, true));

                if ($result['result'] === 'success') {
                    $order->payment_complete();
                    $order->add_order_note('Test payment processed successfully');
                    WP_CLI::success("Payment successful for order #{$order->get_id()}");
                } else {
                    $error_message = isset($result['messages']) ? $result['messages'] : 'Unknown error';
                    WP_CLI::debug("Payment error details: " . print_r($result, true));
                    throw new Exception($error_message);
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