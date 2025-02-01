<?php

// Ensure we're using WP-CLI
if (!defined('WP_CLI')) {
    return;
}

class HPOS_Data_Repair {
    public function repair_orders() {
        WP_CLI::log('Starting HPOS data repair...');
        
        // Get all affected order IDs from the error output
        $affected_orders = [395, 403, 405, 417, 419, 397, 399, 401, 409, 411, 415, 421, 
                          423, 425, 427, 429, 431, 432, 450, 452, 465, 467, 469, 471, 
                          473, 495, 496, 497, 501, 502, 503, 542];
        
        foreach ($affected_orders as $order_id) {
            try {
                WP_CLI::log("Repairing order #$order_id");
                
                // Get order using CRUD
                $order = wc_get_order($order_id);
                if (!$order) {
                    WP_CLI::warning("Order #$order_id not found");
                    continue;
                }

                // Get all post meta directly
                $post_meta = get_post_meta($order_id);
                
                // Repair billing email
                $billing_email = isset($post_meta['_billing_email'][0]) ? $post_meta['_billing_email'][0] : '';
                if ($billing_email) {
                    $order->set_billing_email($billing_email);
                }

                // Rebuild billing address
                $billing_fields = [
                    'first_name', 'last_name', 'company', 'address_1', 'address_2',
                    'city', 'state', 'postcode', 'country', 'phone', 'email'
                ];

                $billing_address = [];
                foreach ($billing_fields as $field) {
                    $meta_key = "_billing_$field";
                    $billing_address[$field] = isset($post_meta[$meta_key][0]) ? $post_meta[$meta_key][0] : '';
                }

                // Rebuild shipping address
                $shipping_fields = [
                    'first_name', 'last_name', 'company', 'address_1', 'address_2',
                    'city', 'state', 'postcode', 'country'
                ];

                $shipping_address = [];
                foreach ($shipping_fields as $field) {
                    $meta_key = "_shipping_$field";
                    $shipping_address[$field] = isset($post_meta[$meta_key][0]) ? $post_meta[$meta_key][0] : '';
                }

                // Set addresses
                $order->set_address($billing_address, 'billing');
                $order->set_address($shipping_address, 'shipping');

                // Rebuild address indexes
                $billing_index = implode(' ', array_filter([
                    $billing_address['first_name'],
                    $billing_address['last_name'],
                    $billing_address['company'],
                    $billing_address['address_1'],
                    $billing_address['address_2'],
                    $billing_address['city'],
                    $billing_address['state'],
                    $billing_address['postcode'],
                    $billing_address['country'],
                    $billing_address['email'],
                    $billing_address['phone']
                ]));

                $shipping_index = implode(' ', array_filter([
                    $shipping_address['first_name'],
                    $shipping_address['last_name'],
                    $shipping_address['company'],
                    $shipping_address['address_1'],
                    $shipping_address['address_2'],
                    $shipping_address['city'],
                    $shipping_address['state'],
                    $shipping_address['postcode'],
                    $shipping_address['country']
                ]));

                // Update address indexes
                $order->update_meta_data('_billing_address_index', $billing_index);
                $order->update_meta_data('_shipping_address_index', $shipping_index);

                // Save changes
                $order->save();

                // Force update post meta for address indexes
                update_post_meta($order_id, '_billing_address_index', $billing_index);
                update_post_meta($order_id, '_shipping_address_index', $shipping_index);

                WP_CLI::success("Repaired order #$order_id");

            } catch (Exception $e) {
                WP_CLI::warning("Failed to repair order #$order_id: " . $e->getMessage());
            }
        }

        WP_CLI::success('HPOS data repair completed');
    }
}

// Run the repair
$repair = new HPOS_Data_Repair();
$repair->repair_orders();