<?php
// test-batch-payments.php

class BatchPaymentTest {
    private $test_order_ids = [];
    
    public function __construct() {
        // Ensure WooCommerce is active
        if (!class_exists('WooCommerce')) {
            throw new Exception('WooCommerce is not active');
        }
    }
    
    public function run_all_tests() {
        try {
            $this->test_order_status_registration();
            $this->test_order_creation();
            $this->test_batch_pending_status();
            $this->test_payment_interception();
            $this->test_cron_scheduling();
            echo "All tests completed successfully!\n";
        } catch (Exception $e) {
            echo "Test failed: " . $e->getMessage() . "\n";
        }
        
        // Cleanup
        $this->cleanup_test_orders();
    }
    
    private function test_order_status_registration() {
        // Verify the batch-pending status is registered
        $statuses = wc_get_order_statuses();
        if (!isset($statuses['wc-batch-pending'])) {
            throw new Exception('Batch-pending status not registered');
        }
        echo "✓ Order status registration test passed\n";
    }
    
    private function test_order_creation() {
        // Create a test order
        $order = wc_create_order();
        $order->set_payment_method('stripe');
        $order->save();
        
        $this->test_order_ids[] = $order->get_id();
        
        if (!$order->get_id()) {
            throw new Exception('Failed to create test order');
        }
        echo "✓ Order creation test passed\n";
    }
    
    private function test_batch_pending_status() {
        if (empty($this->test_order_ids)) {
            throw new Exception('No test orders available');
        }
        
        $order = wc_get_order($this->test_order_ids[0]);
        $order->update_status('batch-pending');
        
        // Refresh order object
        $order = wc_get_order($this->test_order_ids[0]);
        if ($order->get_status() !== 'batch-pending') {
            throw new Exception('Failed to update order to batch-pending status');
        }
        echo "✓ Batch pending status test passed\n";
    }
    
    private function test_payment_interception() {
        // Create a new order and trigger the payment interception
        $order = wc_create_order();
        $order->set_payment_method('stripe');
        $order->save();
        
        $this->test_order_ids[] = $order->get_id();
        
        // Trigger the intercept_early_payment function
        do_action('woocommerce_new_order', $order->get_id());
        
        // Refresh order object
        $order = wc_get_order($order->get_id());
        if ($order->get_status() !== 'batch-pending') {
            throw new Exception('Payment interception failed');
        }
        echo "✓ Payment interception test passed\n";
    }
    
    private function test_cron_scheduling() {
        // Test weekly scheduling
        update_option('batch_payment_frequency', 'weekly');
        wp_clear_scheduled_hook('process_batch_payments');
        reschedule_batch_processing();
        
        if (!wp_next_scheduled('process_batch_payments')) {
            throw new Exception('Failed to schedule batch processing');
        }
        echo "✓ Cron scheduling test passed\n";
    }
    
    private function cleanup_test_orders() {
        foreach ($this->test_order_ids as $order_id) {
            wp_delete_post($order_id, true);
        }
        echo "✓ Test cleanup completed\n";
    }
}

// Run the tests
try {
    $tester = new BatchPaymentTest();
    $tester->run_all_tests();
} catch (Exception $e) {
    echo "Error initializing tests: " . $e->getMessage() . "\n";
}
