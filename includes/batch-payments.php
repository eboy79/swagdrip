<?php
// File: wp-content/themes/your-theme/includes/batch-payments.php

if ( defined( 'WP_CLI' ) && WP_CLI ) {

    class BatchPaymentProcessorTest {
        public $test_products = [];
        public $test_orders   = [];

        public function __construct() {
            if ( ! method_exists( $this, 'create_test_products' ) ) {
                WP_CLI::error( 'create_test_products method not defined' );
            }
        }

        public function run_test() {
            WP_CLI::log( '=== Starting Batch Payment Processor Test ===' );

            try {
                $this->cleanup_existing_test_data();
                $this->create_test_products();
                $this->create_batch_pending_orders();
                $this->simulate_batch_payment_processing();
                $this->cleanup_test_data();
            } catch ( Exception $e ) {
                WP_CLI::error( 'Test failed: ' . $e->getMessage() );
                $this->cleanup_test_data();
            }

            WP_CLI::success( '=== Batch Payment Processor Test Completed ===' );
        }

        public function create_test_products() {
            WP_CLI::log( 'Creating test products...' );

            $product_names = [
                'Batch Test Product 1',
                'Batch Test Product 2',
                'Batch Test Product 3'
            ];

            foreach ( $product_names as $name ) {
                try {
                    $product = new WC_Product_Simple();
                    $product->set_name( $name );
                    $product->set_regular_price( rand( 10, 100 ) );
                    $product_id = $product->save();

                    if ( ! $product_id ) {
                        throw new Exception( "Failed to create product: $name" );
                    }

                    $this->test_products[] = $product_id;
                    WP_CLI::log( "Created product: $name (ID: $product_id)" );
                } catch ( Exception $e ) {
                    WP_CLI::warning( "Error creating product $name: " . $e->getMessage() );
                }
            }
        }

        public function create_batch_pending_orders() {
            WP_CLI::log( 'Creating batch pending orders...' );

            if ( empty( $this->test_products ) ) {
                throw new Exception( 'No test products available' );
            }

            // Register custom order status if not already registered.
            if ( ! in_array( 'wc-batch-pending', array_keys( wc_get_order_statuses() ) ) ) {
                register_post_status( 'wc-batch-pending', [
                    'label'                     => 'Batch Pending',
                    'public'                    => true,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'Batch Pending (%s)', 'Batch Pending (%s)' )
                ] );
            }

            // Create test orders.
            for ( $i = 0; $i < 3; $i++ ) {
                try {
                    $order = wc_create_order();

                    // Add a random product.
                    $product_id = $this->test_products[ array_rand( $this->test_products ) ];
                    $product    = wc_get_product( $product_id );
                    $order->add_product( $product, 1 );

                    // Set billing details.
                    $billing_address = [
                        'first_name' => 'Batch Test',
                        'last_name'  => 'Customer ' . ( $i + 1 ),
                        'email'      => "batchtest{$i}@example.com",
                        'phone'      => '123-456-7890',
                        'address_1'  => '123 Test St',
                        'address_2'  => 'Apt 4B',
                        'city'       => 'Test City',
                        'state'      => 'CA',
                        'postcode'   => '90210',
                        'country'    => 'US'
                    ];

                    foreach ( $billing_address as $key => $value ) {
                        $order->{ "set_billing_$key" }( $value );
                    }

                    // Set shipping details (only valid fields).
                    $shipping_fields = [ 'first_name', 'last_name', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country' ];
                    foreach ( $shipping_fields as $key ) {
                        if ( isset( $billing_address[ $key ] ) ) {
                            $order->{ "set_shipping_$key" }( $billing_address[ $key ] );
                        }
                    }

                    // Set payment method.
                    $order->set_payment_method( 'stripe' );
                    $order->set_payment_method_title( 'Credit Card (Stripe)' );

                    // Save customer data using the CRUD method.
                    $order->update_meta_data( '_stripe_customer_info', [
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
                    ] );
                    $order->save();

                    // Optional: debug saved meta.
                    $stripe_info = $order->get_meta( '_stripe_customer_info' );
                    error_log( print_r( $stripe_info, true ) );

                    $order->calculate_totals();
                    $order->save();
                    $order->update_status( 'batch-pending' );

                    $this->test_orders[] = $order->get_id();
                    WP_CLI::log( "Created batch pending order #{$order->get_id()}" );
                } catch ( Exception $e ) {
                    WP_CLI::warning( "Error creating order: " . $e->getMessage() );
                }
            }
        }

        public function simulate_batch_payment_processing() {
            WP_CLI::log( 'Simulating batch payment processing...' );

            $orders = array_filter( array_map( 'wc_get_order', $this->test_orders ) );
            WP_CLI::log( 'Processing ' . count( $orders ) . ' test orders' );

            foreach ( $orders as $order ) {
                try {
                    WP_CLI::log( "Processing order #{$order->get_id()}" );

                    // Retrieve the Stripe gateway.
                    $available_gateways = WC()->payment_gateways->payment_gateways();
                    if ( ! isset( $available_gateways['stripe'] ) ) {
                        throw new Exception( 'Stripe gateway not available' );
                    }

                    $gateway = $available_gateways['stripe'];

                    // Set test payment data.
                    $_POST['stripe_token'] = 'tok_visa';
                    $_POST['payment_method'] = 'stripe';
                    $_POST['wc-stripe-payment-token'] = 'new';

                    $order->update_meta_data( '_stripe_source_id', 'tok_visa' );
                    $order->update_meta_data( '_stripe_intent_id', 'pi_' . uniqid() );
                    $order->update_meta_data( '_stripe_customer_id', 'cus_' . uniqid() );
                    $order->save();

                    $result = $gateway->process_payment( $order->get_id() );

                    if ( $result['result'] === 'success' ) {
                        $order->payment_complete();
                        $order->add_order_note( 'Test payment processed successfully' );
                        WP_CLI::success( "Payment successful for order #{$order->get_id()}" );
                    } else {
                        throw new Exception( isset( $result['messages'] ) ? $result['messages'] : 'Unknown error' );
                    }
                } catch ( Exception $e ) {
                    WP_CLI::warning( "Payment failed for order #{$order->get_id()}: " . $e->getMessage() );
                    $order->update_status( 'failed' );
                    $order->add_order_note( 'Test payment failed: ' . $e->getMessage() );
                }
            }
        }

        public function cleanup_test_data() {
            WP_CLI::log( 'Cleaning up test data...' );

            // Delete test products.
            foreach ( $this->test_products as $product_id ) {
                wp_delete_post( $product_id, true );
                WP_CLI::log( "Deleted product #{$product_id}" );
            }

            // Delete test orders.
            foreach ( $this->test_orders as $order_id ) {
                $order = wc_get_order( $order_id );
                if ( $order ) {
                    $order->delete( true );
                    WP_CLI::log( "Deleted order #{$order_id}" );
                }
            }
        }

        public function cleanup_existing_test_data() {
            WP_CLI::log( 'Cleaning up existing test data...' );

            // Clean up products matching our test product name.
            $existing_products = get_posts( [
                'post_type'      => 'product',
                'posts_per_page' => -1,
                'post_status'    => 'any',
                's'              => 'Batch Test Product'
            ] );

            foreach ( $existing_products as $product ) {
                wp_delete_post( $product->ID, true );
            }

            // Clean up orders via WC_Order_Query.
            $order_query = new WC_Order_Query( [
                'limit'        => -1,
                'status'       => array_keys( wc_get_order_statuses() ),
                'meta_key'     => '_billing_email',
                'meta_value'   => '@example.com',
                'meta_compare' => 'LIKE'
            ] );
            $existing_orders = $order_query->get_orders();

            foreach ( $existing_orders as $order ) {
                $order->delete( true );
            }
        }
    }

    // Register the WP-CLI command.
    WP_CLI::add_command( 'batch-payment-processor', function( $args, $assoc_args ) {
        $processor = new BatchPaymentProcessorTest();
        $processor->run_test();
    } );
}
