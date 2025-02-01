<?php

// Ensure we're using WP-CLI
if (!defined('WP_CLI')) {
    return;
}

class HPOS_Data_Repair_V2 {
    public function repair_orders() {
        global $wpdb;
        WP_CLI::log('Starting HPOS data repair v2...');
        
        // Get all affected order IDs
        $affected_orders = [395, 403, 405, 417, 419, 397, 399, 401, 409, 411, 415, 421, 
                          423, 425, 427, 429, 431, 432, 450, 452, 465, 467, 469, 471, 
                          473, 495, 496, 497, 501, 502, 503, 542];

        foreach ($affected_orders as $order_id) {
            try {
                WP_CLI::log("Repairing order #$order_id");
                
                // Get original post data directly from posts table
                $original_post = $wpdb->get_row($wpdb->prepare(
                    "SELECT post_modified, post_modified_gmt FROM {$wpdb->posts} WHERE ID = %d",
                    $order_id
                ));

                if (!$original_post) {
                    WP_CLI::warning("Order #$order_id not found in posts table");
                    continue;
                }

                // Get order using CRUD
                $order = wc_get_order($order_id);
                if (!$order) {
                    WP_CLI::warning("Order #$order_id not found via CRUD");
                    continue;
                }

                // Get all post meta directly
                $post_meta = get_post_meta($order_id);

                // Preserve original billing email
                $billing_email = '';
                if (isset($post_meta['_billing_email'][0]) && !empty($post_meta['_billing_email'][0])) {
                    $billing_email = $post_meta['_billing_email'][0];
                    
                    // Update both custom table and post meta
                    update_post_meta($order_id, '_billing_email', $billing_email);
                    $wpdb->update(
                        $wpdb->prefix . 'wc_orders_meta',
                        ['meta_value' => $billing_email],
                        [
                            'order_id' => $order_id,
                            'meta_key' => '_billing_email'
                        ]
                    );
                }

                // Restore original timestamps in custom orders table
                $wpdb->update(
                    $wpdb->prefix . 'wc_orders',
                    [
                        'date_updated' => $original_post->post_modified,
                        'date_updated_gmt' => $original_post->post_modified_gmt
                    ],
                    ['id' => $order_id]
                );

                // Handle the post slug for order #542
                if ($order_id === 542) {
                    $wpdb->delete(
                        $wpdb->prefix . 'wc_orders_meta',
                        [
                            'order_id' => $order_id,
                            'meta_key' => '_wp_desired_post_slug'
                        ]
                    );
                }

                // Force sync timestamps between posts and custom tables
                $wpdb->query($wpdb->prepare("
                    UPDATE {$wpdb->prefix}wc_orders o 
                    JOIN {$wpdb->posts} p ON o.id = p.ID 
                    SET o.date_updated = p.post_modified,
                        o.date_updated_gmt = p.post_modified_gmt
                    WHERE o.id = %d
                ", $order_id));

                WP_CLI::success("Repaired order #$order_id" . ($billing_email ? " with email: $billing_email" : ''));

            } catch (Exception $e) {
                WP_CLI::warning("Failed to repair order #$order_id: " . $e->getMessage());
            }
        }

        WP_CLI::success('HPOS data repair completed');

        // Final verification
        WP_CLI::log('Running final verification...');
        $verify_result = WP_CLI::launch_self('wc', ['hpos', 'verify_data'], [], false, true);
        WP_CLI::log($verify_result->stdout);
    }
}

// Run the repair
$repair = new HPOS_Data_Repair_V2();
$repair->repair_orders();