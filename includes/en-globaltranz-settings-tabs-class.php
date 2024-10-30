<?php

/**
 * Plugin Settings Tabs | plugin settings tabs into wooCommerce settings
 * @package     Woocommerce GlobalTranz Edition
 * @author      <https://eniture.com/>
 * @version     v.1..0 (01/10/2017)
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin Settings Tabs | plugin settings tabs into wooCommerce settings
 */
if (!class_exists('Engtz_GlobalTranz_Settings_Tabs_Class')) {

    class Engtz_GlobalTranz_Settings_Tabs_Class extends WC_Settings_Page
    {

        /**
         * settings tabs class constructor
         */
        public function __construct()
        {
            $this->id = 'globaltranz';
            add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
            add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
            add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
            add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));
        }

        /**
         * adding tabs name to existing tabs in wooCommerce settings
         * @param $settings_tabs
         * @return array
         */
        public function add_settings_tab($settings_tabs)
        {
            $settings_tabs[$this->id] = __('GlobalTranz', 'woocommerce-settings-cerasis_quotes');
            return $settings_tabs;
        }

        /**
         * Settings sections names
         * @return array
         */
        public function get_sections()
        {
            $sections = array(
                '' => __('Connection Settings', 'woocommerce-settings-cerasis_quotes'),
                'section-1' => __('Carriers', 'woocommerce-settings-cerasis_quotes'),
                'section-2' => __('Quote Settings', 'woocommerce-settings-cerasis_quotes'),
                'section-3' => __('Warehouses', 'woocommerce-settings-cerasis_quotes'),
                'shipping-rules' => __('Shipping Rules', 'woocommerce-settings-cerasis_quotes'),
                'section-5' => __('FreightDesk Online', 'woocommerce-settings-cerasis_quotes'),
                'section-6' => __('Validate Addresses', 'woocommerce-settings-cerasis_quotes'),
                'section-4' => __('User Guide', 'woocommerce-settings-cerasis_quotes'),
            );

            // Logs data
            $enable_logs = get_option('wc_settings_gtz_enable_logs');
            if ($enable_logs == 'yes') {
                $sections['en-logs'] = 'Logs';
            }

            $sections = apply_filters('en_woo_pallet_addons_sections', $sections, engtz_cerasis_freights);
            return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
        }

        /**
         * section page for warehouse and dropship settings
         */
        public function warehouse_dropship_page()
        {
            require_once plugin_dir_path(__FILE__) . 'warehouse-dropship/wild/warehouse/warehouse_template.php';
            require_once plugin_dir_path(__FILE__) . 'warehouse-dropship/wild/dropship/dropship_template.php';
        }

        /**
         * section page for user guide
         */
        public function user_guide_page()
        {
            include_once plugin_dir_path(__FILE__) . 'templates/en-globaltranz-user-guide.php';
        }

        /**
         * get settings for all sections
         * @param $section
         * @return string
         */
        public function get_settings($section = null)
        {
            include_once plugin_dir_path(__FILE__) . 'templates/en-globaltranz-test-connection.php';
            include_once plugin_dir_path(__FILE__) . 'templates/en-globaltranz-quote-settings.php';
            include_once plugin_dir_path(__FILE__) . 'templates/en-globaltranz-carriers-page.php';
            $conn_set_obj = new Engtz_Cerasis_Test_Connection_Page();
            $quote_set_obj = new Engtz_Cerasis_Quote_Settings();
            $cerrier_obj = new Engtz_Cerasis_Carriers_Page();
            ob_start();
            switch ($section) {

                case 'section-0' :
                    echo '<div class="cerasis_connection_section_class">';
                    $settings = $conn_set_obj->connection_setting_tab();
                    break;

                case 'section-1':
                    echo '<div class="carrier_section_class">';
                    $settings = $cerrier_obj->carrier_list_tab();
                    break;

                case 'section-2':
                    echo '<div class="engtz_quote_section_main_class_ltl">';
                    $settings = $quote_set_obj->quote_settings_tab();
                    break;

                case 'section-3' :
                    $this->warehouse_dropship_page();
                    $settings = array();
                    break;

                case 'shipping-rules' :
                    $this->shipping_rules_page();
                    $settings = array();
                    break;

                case 'section-4' :
                    $this->user_guide_page();
                    $settings = array();
                    break;

                case 'section-5' :
                    $this->freightdesk_online_section();
                    $settings = [];
                    break;
                case 'section-6' :
                    $this->validate_addresses_section();
                    $settings = [];
                    break;
                
                case 'en-logs' :
                    $this->shipping_logs_section();
                    $settings = [];
                    break;

                default:
                    echo '<div class="cerasis_connection_section_class">';
                    $settings = $conn_set_obj->connection_setting_tab();
                    break;
            }

            $settings = apply_filters('en_woo_addons_settings', $settings, $section, engtz_cerasis_freights);
            $settings = apply_filters('en_woo_pallet_addons_settings', $settings, $section, engtz_cerasis_freights);
            $settings = $this->avaibility_addon($settings);
            return apply_filters('wc-settings-cerasis_quotes', $settings, $section);
        }

        /**
         * avaibility_addon
         * @param array type $settings
         * @return array type
         */
        function avaibility_addon($settings)
        {
            if (is_plugin_active('residential-address-detection/residential-address-detection.php')) {
                unset($settings['avaibility_lift_gate']);
                unset($settings['avaibility_auto_residential']);
            }

            return $settings;
        }

        /**
         * output function calling
         * @global current_section
         */
        public function output()
        {
            global $current_section;
            $settings = $this->get_settings($current_section);
            WC_Admin_Settings::output_fields($settings);
        }

        /**
         * saving all settings to wooCommerce settings
         * @global $current_section
         */
        public function save()
        {
            global $current_section;
            if ($current_section != 'section-1') {
                $settings = $this->get_settings($current_section);
                // Cuttoff Time
                if (isset($_POST['gt_freight_order_cut_off_time']) && $_POST['gt_freight_order_cut_off_time'] != '') {
                    $time_24_format = $this->gt_get_time_in_24_hours($_POST['gt_freight_order_cut_off_time']);
                    $_POST['gt_freight_order_cut_off_time'] = $time_24_format;
                }

                $backup_rates_fields = ['gtz_ltl_backup_rates_fixed_rate', 'gtz_ltl_backup_rates_cart_price_percentage', 'gtz_ltl_backup_rates_weight_function'];
                foreach ($backup_rates_fields as $field) {
                    if (isset($_POST[$field])) update_option($field, $_POST[$field]);
                }

                WC_Admin_Settings::save_fields($settings);
                if (isset($_POST['wc_settings_global_tranz_password'])) {
                    $password = wp_unslash($_POST['wc_settings_global_tranz_password']);
                    update_option('wc_settings_global_tranz_password', $password);
                }
            }
        }

        /**
         * Cuttoff Time
         * @param $timeStr
         * @return false|string
         */
        public function gt_get_time_in_24_hours($timeStr)
        {
            $cutOffTime = explode(' ', $timeStr);
            $hours = $cutOffTime[0];
            $separator = $cutOffTime[1];
            $minutes = $cutOffTime[2];
            $meridiem = $cutOffTime[3];
            $cutOffTime = "{$hours}{$separator}{$minutes} $meridiem";
            return date("H:i", strtotime($cutOffTime));
        }

        /**
         * FreightDesk Online section
         */
        public function freightdesk_online_section()
        {
            include_once plugin_dir_path(__FILE__) . '../fdo/freightdesk-online-section.php';
        }

        /**
         * Validate Addresses Section
         */
        public function validate_addresses_section()
        {
            include_once plugin_dir_path(__FILE__) . '../fdo/validate-addresses-section.php';
        }

        /**
         * Shipping Logs Section
        */
        public function shipping_logs_section()
        {
            include_once plugin_dir_path(__FILE__) . '/templates/logs/en-logs.php';
        }

        public function shipping_rules_page() 
        {
            include_once plugin_dir_path(__FILE__) . '/templates/shipping-rules/shipping-rules-template.php';
        }
    }

    new Engtz_GlobalTranz_Settings_Tabs_Class();
}