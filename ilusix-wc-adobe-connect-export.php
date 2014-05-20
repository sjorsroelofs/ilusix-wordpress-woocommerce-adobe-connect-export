<?php
/**
 * Plugin Name: WooCommerce Adobe Connect CSV export by Ilusix
 * Plugin URI: 
 * Description: Export your WooCommerce orders to a CSV file which you can use to add users in Adobe Connect
 * Version: 1.0.0
 * Author: Sjors Roelofs
 * Author URI: http://www.ilusix.nl
 * License: MIT
 
    The MIT License (MIT)
    
    Copyright (c) 2014 Sjors Roelofs (sjors.roelofs@gmail.com)
    
    Permission is hereby granted, free of charge, to any person obtaining a copy of
    this software and associated documentation files (the "Software"), to deal in
    the Software without restriction, including without limitation the rights to
    use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
    the Software, and to permit persons to whom the Software is furnished to do so,
    subject to the following conditions:
    
    The above copyright notice and this permission notice shall be included in all
    copies or substantial portions of the Software.
    
    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
    FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
    COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
    IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
    CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */


// Create the admin menu
add_action( 'admin_menu', 'iwcace_plugin_menu' );
function iwcace_plugin_menu() {
    add_menu_page( 'Adobe Connect export options', 'Adobe Connect export', 'manage_options', 'ilusix-wc-adobe-connect-export', 'iwcace_plugin_options' );
}

function iwcace_plugin_options() {
    if ( !current_user_can( 'manage_options' ) ) wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    require_once( 'admin/plugin-options.php' );
}

function iwcace_get_woocommerce_status() {
    if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return true;
    return false;
}

function iwcace_query_products() {
    global $wpdb;
    return $wpdb->get_results("SELECT `ID`, `post_title` FROM `" . $wpdb->base_prefix . "posts` WHERE `post_type` = 'product' AND `post_status` = 'publish'");
}

function iwcace_get_product_info($productId) {
    global $wpdb;
    
    $product = $wpdb->get_results("SELECT `ID`, `post_title` FROM `" . $wpdb->base_prefix . "posts` WHERE `post_type` = 'product' AND `post_status` = 'publish' AND `ID` = '" . $productId . "'");
    
    if(count($product)) return $product[0];
    return false;
}

function iwcace_list_products() {
    $products = iwcace_query_products();
    
    if(count($products)) {
    
        echo '<h3>1) Select a product:</h3>';
        
        echo '<ul>';
            foreach($products as $product)
                echo '<li><a href="' . admin_url( 'admin.php' ) . '?page=ilusix-wc-adobe-connect-export&action=list_orders&productId=' . $product->ID . '">' . $product->post_title . '</a></li>';
        echo '</ul>';
    } else {
        echo '<p>There are no products</p>';
    }
}

function iwcace_query_orders_for_product($productId) {
    if(iwcace_get_product_info($productId)) {
        global $wpdb;
        
        $postsTable             = $wpdb->base_prefix . 'posts';
        $postMetaTable          = $wpdb->base_prefix . 'postmeta';
        $wcOrderItemsTable      = $wpdb->base_prefix . 'woocommerce_order_items';
        $wcOrderItemsMetaTable  = $wpdb->base_prefix . 'woocommerce_order_itemmeta';
        
        $ordersResult = $wpdb->get_results("
            SELECT *
            FROM `" . $postsTable . "`
            INNER JOIN `" . $wcOrderItemsTable . "`
                ON `" . $postsTable . "`.`ID` = `" . $wcOrderItemsTable . "`.`order_id`
            INNER JOIN `" . $wcOrderItemsMetaTable . "`
                ON `" . $wcOrderItemsTable . "`.`order_item_id` = `" . $wcOrderItemsMetaTable . "`.`order_item_id`
            INNER JOIN `" . $postMetaTable . "`
                ON `" . $postsTable . "`.`ID` = `" . $postMetaTable . "`.`post_id`
            WHERE `" . $postsTable . "`.`post_type` = 'shop_order'
            AND `" . $wcOrderItemsMetaTable . "`.`meta_key` = '_product_id'
            AND `" . $wcOrderItemsMetaTable . "`.`meta_value` = " . $productId . "
        ");
        
        $orders = array();
        
        foreach($ordersResult as $order) {
            $orders[$order->ID]['ID'] = $order->ID;
            $orders[$order->ID]['post_status'] = $order->post_status;
            $orders[$order->ID]['guid'] = $order->guid;
            $orders[$order->ID]['meta'][$order->meta_key] = $order->meta_value;
        }
        
        if(count($orders)) return $orders;
        return false;
    } else {
        return false;
    }
}

function iwcace_list_orders($productId) {
    if($product = iwcace_get_product_info($productId)) {

        echo '<h3>2) Select the users you want to export for product \'' . $product->post_title . '\':</h3>';
        
        if($orders = iwcace_query_orders_for_product($productId)) {
            
            echo '<form method="post" action="' . admin_url( 'admin.php' ) . '?page=ilusix-wc-adobe-connect-export&action=create_csv&productId=' . $product->ID . '">';
                echo '<ul>';
                    foreach($orders as $order) {
                        echo '<li><label><input type="checkbox" name="order_' . $order['ID'] . '-product_' . $productId . '" checked="checked" /> ' . $order['meta']['_shipping_first_name'] . ' ' . $order['meta']['_shipping_last_name'] . '</label></li>';
                    }
                echo '</ul>';
                
                echo '<br/><input type="submit" class="button button-primary" value="Create CSV" />';
            echo '</form>';
            
        } else {
            echo '<p>There are no orders for this product.</p>';
        }
        
    } else {
        echo '<p>Product not found.</p>';
    }
}

function iwcace_query_order($orderId, $productId) {
    global $wpdb;
    
    $postsTable             = $wpdb->base_prefix . 'posts';
    $postMetaTable          = $wpdb->base_prefix . 'postmeta';
    $wcOrderItemsTable      = $wpdb->base_prefix . 'woocommerce_order_items';
    $wcOrderItemsMetaTable  = $wpdb->base_prefix . 'woocommerce_order_itemmeta';
    
    $orderResult = $wpdb->get_results("
        SELECT *
        FROM `" . $postsTable . "`
        INNER JOIN `" . $wcOrderItemsTable . "`
            ON `" . $postsTable . "`.`ID` = `" . $wcOrderItemsTable . "`.`order_id`
        INNER JOIN `" . $wcOrderItemsMetaTable . "`
            ON `" . $wcOrderItemsTable . "`.`order_item_id` = `" . $wcOrderItemsMetaTable . "`.`order_item_id`
        INNER JOIN `" . $postMetaTable . "`
            ON `" . $postsTable . "`.`ID` = `" . $postMetaTable . "`.`post_id`

        WHERE `" . $postsTable . "`.`ID` = " . $orderId . "
    ");
    
    $order = array();
    foreach($orderResult as $orderRes) {
        $order['ID'] = $orderRes->ID;
        $order['post_status'] = $orderRes->post_status;
        $order['guid'] = $orderRes->guid;
        $order['meta'][$orderRes->meta_key] = $orderRes->meta_value;
    }
    
    if(count($order)) return $order;
    return false;
}

function iwcace_create_csv($productId) {
    $pluginDir = WP_PLUGIN_DIR . '/ilusix-wc-adobe-connect-export/';

    if(count($_POST)) {
        $orderIds = array();
        
        $count = 0;
        foreach($_POST as $key => $value) {
            if($value == 'on') {
                $explode = explode('-', $key);
                
                $orderIds[$count]['orderId'] = str_replace('order_', '', $explode[0]);
                $orderIds[$count]['productId'] = str_replace('product_', '', $explode[1]);
                
                $count++;
            }
        }
        
        $orders = array();
        foreach($orderIds as $key => $value) {
            $orders[$value['orderId']] = iwcace_query_order($value['orderId'], $value['productId']);
        }
        
        
        if(!file_exists($pluginDir . 'exports')) {
            mkdir($pluginDir . 'exports', 0777);
        }
        
        
        $oldExports = glob($pluginDir . 'exports/*');
        foreach($oldExports as $file) {
            if(is_file($file)) {
                unlink($file);
            }
        }
        
        
        $exportFileClean = 'exports/export-' . date('d_m_Y') . '-' . uniqid() . '.csv';
        $exportFile = $pluginDir . $exportFileClean;
        $exportFileClean = plugin_dir_url(__FILE__) . $exportFileClean;
        
        $fileHandle = fopen($exportFile, 'w');
        fclose($fileHandle);

        $fileContent = file_get_contents($exportFile);
        $fileContent .= "first-name,last-name,login,email,password\n";
        
        foreach($orders as $order) {
            $fileContent .= $order['meta']['_billing_first_name'] . ',' . $order['meta']['_billing_last_name'] . ',' . $order['meta']['_billing_email'] . ',' . $order['meta']['_billing_email'] . ',' . substr(sha1(uniqid()), 0, 8) . "\n";
            file_put_contents($exportFile, $fileContent);
        }

        return $exportFileClean;
    } else {
        return false;
    }
}