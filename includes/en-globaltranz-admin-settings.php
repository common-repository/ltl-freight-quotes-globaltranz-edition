<?php

/**
 * Admin Settings | all admin settings defined
 * @package     Woocommerce GlobalTranz Edition
 * @author      <https://eniture.com/>
 * @version     v.1..0 (01/10/2017)
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}


/**
 * Admin Settings | all admin settings defined for plugin usage
 */
if (!class_exists('Engtz_Admin_Settings')) {

    class Engtz_Admin_Settings
    {

        /**
         * admin settings constructor
         */
        public function __construct()
        {
            add_action('init', array($this, 'engtz_save_carrier_status'));
            add_action('init', array($this, 'engtz_save_carrier'));
            add_action('admin_enqueue_scripts', array($this, 'admin_validation_styles_scripts'));
            add_filter('woocommerce_package_rates', array($this, 'hide_shipping_based_on_class'));
            if (!function_exists('create_ltl_class')) {
                $this->create_ltl_class();
            }
            add_filter('woocommerce_no_shipping_available_html', array($this, 'cerasis_shipping_message'));
            add_filter('woocommerce_cart_no_shipping_available_html', array($this, 'cerasis_shipping_message'));

        }

        /**
         * Load CSS And JS Scripts
         */
        public function admin_validation_styles_scripts()
        {
            wp_register_style('custom_style', plugin_dir_url(dirname(__FILE__)) . 'assets/css/en-globaltranz-custom-style.css', array(), '1.1.7', 'screen');
            wp_enqueue_style('custom_style');
        }

        /**
         * Save Freight Carriers
         * @param $post_id
         */
        public function engtz_save_carrier_status($post_id)
        {
            $actionStatus = (isset($_POST['action'])) ? sanitize_text_field($_POST['action']) : "";

            if (isset($actionStatus) && $actionStatus == 'engtz_save_carrier_status') {
                global $wpdb;
                $carriers_table = $wpdb->prefix . "en_cerasis_account_carriers";
                $ltl_carriers = $wpdb->get_results("SELECT * FROM " . $carriers_table . " WHERE `plugin_name` = 'cerasis_ltl_carriers' ORDER BY `carrier_name` ASC");
                foreach ($ltl_carriers as $carriers_value):
                    $carrier_scac = (isset($_POST[$carriers_value->carrier_scac . $carriers_value->id])) ? sanitize_text_field($_POST[$carriers_value->carrier_scac . $carriers_value->id]) : "";
                    $liftgate_fee = (isset($_POST[$carriers_value->carrier_scac . $carriers_value->id . "liftgate_fee"])) ? sanitize_text_field($_POST[$carriers_value->carrier_scac . $carriers_value->id . "liftgate_fee"]) : "";
                    if (isset($carrier_scac) && $carrier_scac == 'on') {
                        $wpdb->query($wpdb->prepare("UPDATE " . $carriers_table . " SET `carrier_status` = '%s' , `liftgate_fee` = '$liftgate_fee' WHERE `carrier_scac` = '$carriers_value->carrier_scac' AND `plugin_name` Like 'cerasis_ltl_carriers'", '1'));
                    } else {

                        $wpdb->query($wpdb->prepare("UPDATE " . $carriers_table . " SET `carrier_status` = '%s' , `liftgate_fee` = '$liftgate_fee' WHERE `carrier_scac` = '$carriers_value->carrier_scac' AND `plugin_name` Like 'cerasis_ltl_carriers' ", '0'));
                    }
                endforeach;
            }
        }
        /**
         * Save Globaltranz Carriers
         * @param $post_id
         */
        public function engtz_save_carrier($post_id)
        {
            $actionStatus = (isset($_POST['action'])) ? sanitize_text_field($_POST['action']) : "";

            if (isset($actionStatus) && $actionStatus == 'engtz_save_carrier') {
                global $wpdb;
                $carriers_table = $wpdb->prefix . "gt_carriers";
                $ltl_carriers = $wpdb->get_results("SELECT * FROM " . $carriers_table . " ORDER BY gtz_name ASC");

                foreach ($ltl_carriers as $carriers_value):
                    $gtz_scac = (isset($_POST[$carriers_value->gtz_scac . $carriers_value->id])) ? sanitize_text_field($_POST[$carriers_value->gtz_scac . $carriers_value->id]) : "";

                    if (isset($gtz_scac) && $gtz_scac == 'on') {
                        $wpdb->query($wpdb->prepare("UPDATE " . $carriers_table . " SET `carrier_status` = '%s' WHERE `gtz_scac` = '$carriers_value->gtz_scac'", '1'));
                    } else {

                        $wpdb->query($wpdb->prepare("UPDATE " . $carriers_table . " SET `carrier_status` = '%s' WHERE `gtz_scac` = '$carriers_value->gtz_scac'", '0'));
                    }
                endforeach;
            }
        }

        /**
         * Hide Shipping Methods If Not From Eniture
         * @param $available_methods
         */
        function hide_shipping_based_on_class($available_methods)
        {
            // flag to check if rates available of current plugin
            $plugin_rates_available = false;
            foreach ($available_methods as $value) {
                if (strpos($value->id, 'backup_rates') !== false) continue;

                if ($value->method_id == 'engtz_cerasis_shipping_method' || strpos($value->id, 'engtz_cerasis_shipping_method') !== false) {
                    $plugin_rates_available = true;
                    break;
                }
            }
            
            $plugin_rates = (empty(get_option('gtz_ltl_backup_rates_display')) || get_option('gtz_ltl_backup_rates_display') == 'no_plugin_rates') && $plugin_rates_available;
            $other_rates = get_option('gtz_ltl_backup_rates_display') == 'no_other_rates' && count($available_methods) > 1;

            // Remove backup Rates in case other rates are available
            if (get_option('enable_backup_rates_gtz_ltl') == 'yes' && ($plugin_rates || $other_rates)) {
                $backup_rate_id = 'engtz_cerasis_shipping_method:backup_rates';
                foreach ($available_methods as $key => $value) {
                    if (isset($value->id) && $value->id == $backup_rate_id) {
                        unset($available_methods[$key]);
                    }
                }
            }

            if (get_option('wc_settings_cerasis_allow_other_plugins') == 'no'
                && count($available_methods) > 0) {
                $plugins_array = array();
                $eniture_plugins = get_option('EN_Plugins');
                if ($eniture_plugins) {
                    $plugins_array = json_decode($eniture_plugins, true);
                }
        
                // add methods which not exist in array
                $plugins_array[] = 'ltl_shipping_method';
                $plugins_array[] = 'daylight';
                $plugins_array[] = 'tql';
                $plugins_array[] = 'unishepper_small';
                $plugins_array[] = 'usps';
        
                if ($plugin_rates_available) {
                    foreach ($available_methods as $index => $method) {
                        if (!in_array($method->method_id, $plugins_array)) {
                            unset($available_methods[$index]);
                        }
                    }
                }
            }
            return $available_methods;
        }

        /**
         * getting handling fee
         */
        public function get_handling_fee()
        {
            return $handling_fee = get_option('wc_settings_cerasis_hand_free_mark_up');
        }

        /**
         * check status for other plugins
         */
        public function other_plugins_status()
        {
            return $other_plugin_status = get_option('wc_settings_cerasis_allow_other_plugins');
        }

        /**
         * create LTL class function
         */
        static function create_ltl_class($network_wide = null)
        {
            if ( is_multisite() && $network_wide ) {

                foreach (get_sites(['fields'=>'ids']) as $blog_id) {
                    switch_to_blog($blog_id);
                    wp_insert_term('LTL Freight', 'product_shipping_class', array(
                            'description' => 'The plugin is triggered to provide LTL freight quote when the shopping cart contains an item that has a designated shipping class. Shipping class? is a standard WooCommerce parameter not to be confused with freight class? or the NMFC classification system.',
                            'slug' => 'ltl_freight'
                        )
                    );
                    restore_current_blog();
                }

            } else {
                wp_insert_term('LTL Freight', 'product_shipping_class', array(
                        'description' => 'The plugin is triggered to provide LTL freight quote when the shopping cart contains an item that has a designated shipping class. Shipping class? is a standard WooCommerce parameter not to be confused with freight class? or the NMFC classification system.',
                        'slug' => 'ltl_freight'
                    )
                );
            }
        }

        /**
         * No Shipping Available Message
         * @param $message
         * @return string
         */
        function cerasis_shipping_message($message)
        {
            return __('There are no carriers available for this shipment please contact with store owner');
        }

    }

}

/**
 * Filter For CSV Import
 */
if (!function_exists('en_import_dropship_location_csv')) 
{

    /**
     * Import drop ship location CSV
     * @param $data
     * @param $this
     * @return array
     */
    function en_import_dropship_location_csv($data, $parseData)
    {
        $_product_freight_class = $_product_freight_class_variation = '';
        $_dropship_location = $locations = [];
        foreach ($data['meta_data'] as $key => $metaData) {
            $location = explode(',', trim($metaData['value']));
            switch ($metaData['key']) {
                // Update new columns
                case '_product_freight_class':
                    $_product_freight_class = trim($metaData['value']);
                    unset($data['meta_data'][$key]);
                    break;
                case '_product_freight_class_variation':
                    $_product_freight_class_variation = trim($metaData['value']);
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_nickname':
                    $locations[0] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_zip_code':
                    $locations[1] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_city':
                    $locations[2] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_state':
                    $locations[3] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_country':
                    $locations[4] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location':
                    $_dropship_location = $location;
            }
        }

        // Update new columns
        if (strlen($_product_freight_class) > 0) {
            $data['meta_data'][] = [
                'key' => '_ltl_freight',
                'value' => $_product_freight_class,
            ];
        }

        // Update new columns
        if (strlen($_product_freight_class_variation) > 0) {
            $data['meta_data'][] = [
                'key' => '_ltl_freight_variation',
                'value' => $_product_freight_class_variation,
            ];
        }

        if (!empty($locations) || !empty($_dropship_location)) {
            if (isset($locations[0]) && is_array($locations[0])) {
                foreach ($locations[0] as $key => $location_arr) {
                    $metaValue = [];
                    if (isset($locations[0][$key], $locations[1][$key], $locations[2][$key], $locations[3][$key])) {
                        $metaValue[0] = $locations[0][$key];
                        $metaValue[1] = $locations[1][$key];
                        $metaValue[2] = $locations[2][$key];
                        $metaValue[3] = $locations[3][$key];
                        $metaValue[4] = $locations[4][$key];
                        $dsId[] = en_serialize_dropship($metaValue);
                    }
                }
            } else {
                $dsId[] = en_serialize_dropship($_dropship_location);
            }

            $sereializedLocations = maybe_serialize($dsId);
            $data['meta_data'][] = [
                'key' => '_dropship_location',
                'value' => $sereializedLocations,
            ];
        }
        return $data;
    }

}

if (!function_exists('en_serialize_dropship')) 
{
/**
 * Serialize drop ship
 * @param $metaValue
 * @return string
 * @global $wpdb
 */
    function en_serialize_dropship($metaValue)
    {
        global $wpdb;
        $dropship = (array)reset($wpdb->get_results(
            "SELECT id
                    FROM " . $wpdb->prefix . "warehouse WHERE nickname='$metaValue[0]' AND zip='$metaValue[1]' AND city='$metaValue[2]' AND state='$metaValue[3]' AND country='$metaValue[4]'"
        ));

        $dropship = array_map('intval', $dropship);

        if (empty($dropship['id'])) {
            $data = en_csv_import_dropship_data($metaValue);
            $wpdb->insert(
                $wpdb->prefix . 'warehouse', $data
            );

            $dsId = $wpdb->insert_id;
        } else {
            $dsId = $dropship['id'];
        }

        return $dsId;
    }
}

if (!function_exists('en_csv_import_dropship_data')) 
{
/**
 * Filtered Data Array
 * @param $metaValue
 * @return array
 */
    function en_csv_import_dropship_data($metaValue)
    {
        return array(
            'city' => $metaValue[2],
            'state' => $metaValue[3],
            'zip' => $metaValue[1],
            'country' => $metaValue[4],
            'location' => 'dropship',
            'nickname' => (isset($metaValue[0])) ? $metaValue[0] : "",
        );
    }
}

add_filter('woocommerce_product_importer_parsed_data', 'en_import_dropship_location_csv', '99', '2');
