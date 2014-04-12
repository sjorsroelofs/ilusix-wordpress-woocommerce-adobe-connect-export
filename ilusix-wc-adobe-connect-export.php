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
    if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        return true;
    } else {
        return false;
    }
}

function iwcace_query_products() {
    global $wpdb;
    return $wpdb->get_results("SELECT `ID`, `post_title` FROM `" . $wpdb->base_prefix . "posts` WHERE `post_type` = 'product' AND `post_status` = 'publish'");
}

function iwcace_list_products() {
    $products = iwcace_query_products();
    
    if(count($products)) {
        echo '<ul>';
            foreach($products as $product) {
                echo '<li>' . $product->post_title . '</li>';
            }
        echo '</ul>';
    
    } else {
        echo '<p>There are no products</p>';
    }
}

function iwcace_list_orders($productId) {
    echo '<p>Listing orders!</p>';
}