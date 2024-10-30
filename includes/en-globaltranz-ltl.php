<?php

/**
 * LTL Freight Quotes |  GlobalTranz Edition
 * @package     Woocommerce GlobalTranz Edition
 * @author      <https://eniture.com/>
 * @version     v.1..0 (01/10/2017)
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LTL Freight Quotes GlobalTranz Edition Class
 */
if (!class_exists('Engtz_GlobalTranz_Ltl')) {

    class Engtz_GlobalTranz_Ltl
    {

        /**
         * plugin end point url for production
         * @var string
         */

        public $end_point_url_pro = '';

        /**
         * Quote Request apiVersion
         * @var double
         */
        public $apiVersion = '1.0';

        /**
         * Quote Request Plateform
         * @var string
         */
        public $plateform = 'wordpress';

        /**
         * Quotes Request Carrier Name
         * @var varchar
         */
        public $carrier_name = 'cerasis';

        /**
         * Quotes Carriers Mode
         * @var string
         */
        public $carrierMode = 'pro';

        /**
         * Quote Type
         * @var string
         */
        public $quotestType = 'ltl';

        /**
         * version variable for plugin version number
         * @var int
         */
        private $v;

        /**
         * LTL Freight Cerasis constructor
         */
        function __construct()
        {
            //Check woocommerce installlation
            if (!is_plugin_active('woocommerce/woocommerce.php')) {
                add_action('admin_notices', array($this, 'wc_cerasis_avaibility_err'));
            } else {
                add_filter('woocommerce_get_settings_pages', array($this, 'cerasis_quotes_settings_tabs'));
            }
            add_action('admin_init', array($this, 'wc_cerasis_check_woo_version'));

            $this->setEndPoint();
        }

        /**
         * plugin version number
         * @return int
         */
        public function version()
        {
            return $this->v = '2.0';
        }

        /**
         * Check WooCommerce availability
         * @return string
         */
        public function wc_cerasis_avaibility_err()
        {
            $class = esc_attr("error");
            $message = "LTL Freight Quotes GlobalTranz Edition is enabled, but not effective. It requires WooCommerce to work, please <a target='_blank' href='https://wordpress.org/plugins/woocommerce/installation/'>Install</a> WooCommerce Plugin.";
            echo "<div class=\"$class\"> <p>" . force_balance_tags($message) . "</p></div>";
        }

        /**
         * WooCommerce version number
         */
        public function wc_cerasis_check_woo_version()
        {
            $woo_version = $this->woo_version_number();
            $version = '3.0.0';
            if (!version_compare($woo_version, $version, ">=")) {
                $this->wc_cerasis_version_failure();
            }
        }

        /**
         * WooCommerce version failure
         * @return string
         */
        public function wc_cerasis_version_failure()
        {
            echo '<div class="notice notice-error"><p>';
            _e('LTL Freight Quotes GlobalTranz Edition plugin requires WooCommerce minimum version 3.0.0 or higher to work. Functionality may not work properly.', 'wc_cerasis_version_failure');
            echo '</p>
        </div>';
        }

        /**
         * WooCommerce version compatibility
         * @return string
         */
        function woo_version_number()
        {
            $plugin_folder = get_plugins('/' . 'woocommerce');
            $plugin_file = 'woocommerce.php';
            if (isset($plugin_folder[$plugin_file]['Version'])) {
                return $plugin_folder[$plugin_file]['Version'];
            } else {
                return NULL;
            }
        }

        /**
         * Add Tab For CerasisQuotes into Woo Settings Page
         * @param $settings
         */
        public function cerasis_quotes_settings_tabs($settings)
        {
            include_once plugin_dir_path(__FILE__) . 'en-globaltranz-settings-tabs-class.php';
            return $settings;
        }

        public function setEndPoint()
        {
            $this->end_point_url_pro = GT_HITTING_DOMAIN_URL . '/v3.0/index.php';
            if (get_option('cerasis_global_tranz_api_endpoint') == 'wc_global_tranz_new_api_fields') {
                $this->end_point_url_pro = GT_NEW_API_HITTING_DOMAIN_URL . '/v3.0/index.php';
            }            
        }
    }

    new Engtz_GlobalTranz_Ltl();
}
