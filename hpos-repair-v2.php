<?php
// Enable error reporting for testing.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// First, add a filter to override the internal meta key check.
// This forces WooCommerce to allow generic meta updates for _billing_email.
add_filter( 'woocommerce_is_internal_meta_key', '__return_false' );

// List the affected order IDs (adjust the list as needed).
$affected_orders = [395, 403, 405, 417, 419];

foreach ( $affected_orders as $order_id ) {
    // Get the order object.
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        WP_CLI::warning("Order #$order_id not found.");
        continue;
    }
    // Retrieve the billing email from the postmeta.
    $billing_email = get_post_meta( $order_id, '_billing_email', true );
    if ( empty( $billing_email ) ) {
        WP_CLI::warning("Order #$order_id has an empty billing email. Skipping.");
        continue;
    }
    // Use the dedicated setter to update the billing email.
    // (WooCommerce provides set_billing_email() for this purpose.)
    $order->set_billing_email( $billing_email );
    // Save the order so that the new value is written into HPOS.
    $order->save();
    WP_CLI::success("Order #$order_id re-saved with billing email: $billing_email");
}

// Remove the temporary filter so that normal internal meta handling resumes.
remove_filter( 'woocommerce_is_internal_meta_key', '__return_false' );

WP_CLI::log("Test update complete. Please now run the verification command.");
