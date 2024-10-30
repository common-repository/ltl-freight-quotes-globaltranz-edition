<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once 'create_order_from_admin.php';
require_once 'en-order-widget.php';
require_once 'rates/order-rates.php';
require 'assets/js/admin_order.php';

add_action('admin_enqueue_scripts', 'engtz_cerasis_admin_script');
if (!function_exists('engtz_cerasis_admin_script')) {

    function engtz_cerasis_admin_script() {
        wp_register_style('cerasis_order', plugin_dir_url(__FILE__) . '/assets/css/admin_order.css', array(), '1.0.1', 'screen');
        wp_enqueue_style('cerasis_order');
    }

}
