<?php
/**
 * Plugin Name:  LTL Freight Quotes – GlobalTranz Edition
 * Plugin URI:   https://eniture.com/products/
 * Description:  Dynamically retrieves your negotiated shipping rates from GlobalTranz and displays the results in the WooCommerce shopping cart.
 * Version:      2.3.11
 * Author:       Eniture Technology
 * Author URI:   http://eniture.com/
 * Text Domain:  eniture-technology
 * License:      GPL version 2 or later - http://www.eniture.com/
 * WC requires at least: 6.4
 * WC tested up to: 9.3.2
 */
if (!defined('ABSPATH')) {
    exit;
}
define('GT_HITTING_DOMAIN_URL', 'https://ws081.eniture.com');
define('GT_NEW_API_HITTING_DOMAIN_URL', 'https://ws001.eniture.com');
define('GT_FDO_HITTING_URL', 'https://freightdesk.online/api/updatedWoocomData');
define('GT_MAIN_FILE', __FILE__);
define('GT_MAIN_DIR', __DIR__);
define('GT_DIRECTORY', plugin_dir_url(__FILE__));
define('GT_FDO_COUPON_BASE_URL', 'https://freightdesk.online');
define('GT_VA_COUPON_BASE_URL', 'https://validate-addresses.com');


add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );


/**
 * check plugin activattion
 */
if (!function_exists('is_plugin_active')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

// Define reference
function engtz_freight_plugin($plugins)
{
    $plugins['lfq'] = (isset($plugins['lfq'])) ? array_merge($plugins['lfq'], ['engtz_cerasis_shipping_method' => 'Engtz_GlobalTranz_Shipping_Method']) : ['engtz_cerasis_shipping_method' => 'Engtz_GlobalTranz_Shipping_Method'];
    return $plugins;
}

add_filter('en_plugins', 'engtz_freight_plugin');
if (!function_exists('engtz_plans_notification_PD')) {

    function engtz_plans_notification_PD($product_detail_options)
    {
        $eniture_plugins_id = 'eniture_plugin_';

        for ($en = 1; $en <= 25; $en++) {
            $settings = get_option($eniture_plugins_id . $en);
            if (isset($settings) && (!empty($settings)) && (is_array($settings))) {
                $plugin_detail = current($settings);
                $plugin_name = (isset($plugin_detail['plugin_name'])) ? $plugin_detail['plugin_name'] : "";

                foreach ($plugin_detail as $key => $value) {
                    if ($key != 'plugin_name') {
                        $action = $value === 1 ? 'enable_plugins' : 'disable_plugins';
                        $product_detail_options[$key][$action] = (isset($product_detail_options[$key][$action]) && strlen($product_detail_options[$key][$action]) > 0) ? ", $plugin_name" : "$plugin_name";
                    }
                }
            }
        }

        return $product_detail_options;
    }

    add_filter('engtz_plans_notification_PD', 'engtz_plans_notification_PD', 10, 1);
}

if (!function_exists('engtz_plans_notification_message')) {

    function engtz_plans_notification_message($enable_plugins, $disable_plugins)
    {
        $enable_plugins = (strlen($enable_plugins) > 0) ? "$enable_plugins: <b> Enabled</b>. " : "";
        $disable_plugins = (strlen($disable_plugins) > 0) ? " $disable_plugins: Upgrade to <b>Standard Plan to enable</b>." : "";
        return $enable_plugins . "<br>" . $disable_plugins;
    }

    add_filter('engtz_plans_notification_message_action', 'engtz_plans_notification_message', 10, 2);
}

if (!function_exists('engtz_woo_plans_nested_notification_message')) {

    function engtz_woo_plans_nested_notification_message($enable_plugins, $disable_plugins, $feature)
    {
        $enable_plugins = (strlen($enable_plugins) > 0) ? "$enable_plugins: <b> Enabled</b>. " : "";
        $disable_plugins = (strlen($disable_plugins) > 0 && $feature == 'nested_material') ? " $disable_plugins: Upgrade to <b>Advance Plan to enable</b>." : "";
        return $enable_plugins . "<br>" . $disable_plugins;
    }

    add_filter('engtz_woo_plans_nested_notification_message_action', 'engtz_woo_plans_nested_notification_message', 10, 3);
}

/**
 * Load scripts for GTZ Freight json tree view
 */
if (!function_exists('en_gtz_cerasis_jtv_script')) {
    function en_gtz_cerasis_jtv_script()
    {
        wp_register_style('en_gtz_cerasis_json_tree_view_style', plugin_dir_url(__FILE__) . 'includes/templates/logs/en-json-tree-view/en-jtv-style.css');
        wp_register_script('en_gtz_cerasis_json_tree_view_script', plugin_dir_url(__FILE__) . 'includes/templates/logs/en-json-tree-view/en-jtv-script.js', ['jquery'], '1.0.0');

        wp_enqueue_style('en_gtz_cerasis_json_tree_view_style');
        wp_enqueue_script('en_gtz_cerasis_json_tree_view_script', [
            'en_tree_view_url' => plugins_url(),
        ]);

         // Shipping rules script and styles
         wp_enqueue_script('en_gtz_ltl_sr_script', plugin_dir_url(__FILE__) . '/includes/templates/shipping-rules/assets/js/shipping_rules.js', array(), '1.0.0');
         wp_localize_script('en_gtz_ltl_sr_script', 'script', array(
             'pluginsUrl' => plugins_url(),
         ));
         wp_register_style('en_gtz_ltl_shipping_rules_section', plugin_dir_url(__FILE__) . '/includes/templates/shipping-rules/assets/css/shipping_rules.css', false, '1.0.0');
         wp_enqueue_style('en_gtz_ltl_shipping_rules_section'); 
    }

    add_action('admin_init', 'en_gtz_cerasis_jtv_script');
}

/**
 * plugin settings and support link at wp plugin.php page
 * @staticvar $plugin
 * @param $actions
 * @param $plugin_file
 * @return string
 */
if (!function_exists('engtz_cerasis_add_action_plugin')) {

    function engtz_cerasis_add_action_plugin($actions, $plugin_file)
    {
        static $plugin;
        if (!isset($plugin))
            $plugin = plugin_basename(__FILE__);
        if ($plugin == $plugin_file) {
            $settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=globaltranz">' . __('Settings', 'General') . '</a>');
            $site_link = array('support' => '<a href="https://support.eniture.com/" target="_blank">Support</a>');
            $actions = array_merge($settings, $actions);
            $actions = array_merge($site_link, $actions);
        }
        return $actions;
    }

    add_filter('plugin_action_links', 'engtz_cerasis_add_action_plugin', 10, 5);
}
/**
 * Get Host
 * @param type $url
 * @return type
 */
if (!function_exists('engtz_getHost')) {

    function engtz_getHost($url)
    {
        $parseUrl = parse_url(trim($url));
        if (isset($parseUrl['host'])) {
            $host = $parseUrl['host'];
        } else {
            $path = explode('/', $parseUrl['path']);
            $host = $path[0];
        }
        return trim($host);
    }

}

/**
 * Get Domain Name
 */
if (!function_exists('engtz_cerasis_get_domain')) {

    function engtz_cerasis_get_domain()
    {
        global $wp;
        $url = home_url($wp->request);
        return engtz_getHost($url);
    }
}
add_action('admin_enqueue_scripts', 'en_globaltranz_script');

/**
 * Load Front-end scripts for globaltranz
 */
function en_globaltranz_script()
{
    wp_enqueue_script('jquery');
    // Cuttoff Time

    wp_register_style('gt_wickedpicker_style', plugin_dir_url(__FILE__) . 'assets/css/wickedpicker.min.css', false, '1.0.0');
    wp_register_script('gt_wickedpicker_script', plugin_dir_url(__FILE__) . 'assets/js/wickedpicker.js', false, '1.0.0');
    wp_enqueue_style('gt_wickedpicker_style');

    wp_enqueue_script('gt_wickedpicker_script');
    wp_enqueue_script('en_globaltranz_script', plugin_dir_url(__FILE__) . 'assets/js/en-globaltranz-settings.js', array(), '1.1.7');
    wp_localize_script('en_globaltranz_script', 'en_globaltranz_admin_script', array(
        'plugins_url' => plugins_url(),
        'allow_proceed_checkout_eniture' => trim(get_option("allow_proceed_checkout_eniture")),
        'prevent_proceed_checkout_eniture' => trim(get_option("prevent_proceed_checkout_eniture")),
        'wc_settings_cerasis_rate_method' => get_option("wc_settings_cerasis_rate_method"),
        // Cuttoff Time
        'gt_freight_order_cutoff_time' => get_option("gt_freight_order_cut_off_time"),
        'gtz_ltl_backup_rates_fixed_rate' => get_option("gtz_ltl_backup_rates_fixed_rate"),
        'gtz_ltl_backup_rates_cart_price_percentage' => get_option("gtz_ltl_backup_rates_cart_price_percentage"),
        'gtz_ltl_backup_rates_weight_function' => get_option("gtz_ltl_backup_rates_weight_function"),
    ));

    
    if(is_admin() && (!empty( $_GET['page']) && 'wc-orders' == $_GET['page'] ) && (!empty( $_GET['action']) && 'new' == $_GET['action'] ))
    {
        if (!wp_script_is('eniture_calculate_shipping_admin', 'enqueued')) {
            wp_enqueue_script('eniture_calculate_shipping_admin', plugin_dir_url(__FILE__) . '/assets/js/eniture-calculate-shipping-admin.js', array(), '1.0.0' );
        }
    }

    if (empty(get_option('gtz_ltl_backup_rates_category'))) {
        update_option('gtz_ltl_backup_rates_category', 'fixed_rate', true);
    }
}

/**
 * Autoloads all classes file called.
 */
require_once 'fdo/en-fdo.php';
require_once 'includes/templates/csv-export.php';
require_once 'includes/templates/en-globaltranz-products-nested-options.php';
require_once 'includes/templates/en-globaltranz-products-stackable-option.php';

require_once GT_MAIN_DIR . '/includes/en-globaltranz-install-uninstall.php';
require_once GT_MAIN_DIR . '/includes/en-globaltranz-admin-settings.php';
require_once GT_MAIN_DIR . '/includes/en-globaltranz-ltl.php';
require_once GT_MAIN_DIR . '/includes/en-globaltranz-cart-to-request.php';
require_once GT_MAIN_DIR . '/includes/en-globaltranz-quotes-request.php';

require_once GT_MAIN_DIR . '/includes/en-globaltranz-connection-request.php';
require_once GT_MAIN_DIR . '/includes/en-globaltranz-liftgate-as-option.php';
require_once GT_MAIN_DIR . '/includes/en-globaltranz-curl-class.php';
require_once GT_MAIN_DIR . '/includes/en-globaltranz-billing-details.php';


new Engtz_Install_Uninstall();
new Engtz_Admin_Settings();
new Engtz_Quotes_Request();
new Engtz_Connection_Request();

require_once(__DIR__ . '/orders/en-order-export.php');
require_once(__DIR__ . '/orders/en-order-widget.php');
require_once(__DIR__ . '/orders/rates/order-rates.php');
require_once('product/en-product-detail.php');
require_once plugin_dir_path(__FILE__) . ('includes/templates/shipping-rules/shipping-rules-save.php');

require_once plugin_dir_path(__FILE__) . 'includes/en-globaltranz-shipping-update-change.php';
include_once plugin_dir_path(__FILE__) . 'includes/carriers/en-globaltranz-carrier-list.php';
require_once(__DIR__ . '/includes/en-globaltranz-filter-quotes.php');
require_once(__DIR__ . '/includes/en-globaltranz-shipping-method.php');
require_once(__DIR__ . '/includes/en-globaltranz-compact.php');
require_once(__DIR__ . '/includes/en-globaltranz-liftgate-as-option.php');
require_once plugin_dir_path(__FILE__) . ('includes/warehouse-dropship/wild-delivery.php');
require_once plugin_dir_path(__FILE__) . ('includes/warehouse-dropship/get-distance-request.php');
require_once plugin_dir_path(__FILE__) . ('includes/standard-package-addon/standard-package-addon.php');
require_once plugin_dir_path(__FILE__) . 'update-plan.php';
require_once __DIR__ . '/api-v2/en-response.php';
require_once __DIR__ . '/api-v2/en-other-rates.php';
require_once 'carrier_list.php';

/**
 * LTL Freight Quotes – GlobalTranz Edition Activation/Deactivation Hook
 */
register_activation_hook(__FILE__, array('Engtz_Admin_Settings', 'create_ltl_class'));
register_activation_hook(__FILE__, array('Engtz_Install_Uninstall', 'install'));
register_activation_hook(__FILE__, array('Engtz_Install_Uninstall', 'gt_freight_update_warehouse'));
register_activation_hook(__FILE__, 'engtz_old_store_cerasis_ltl_dropship_status');
register_activation_hook(__FILE__, 'engtz_old_store_cerasis_ltl_hazmat_status');
register_activation_hook(__FILE__, 'engtz_cerasis_freight_activate_hit_to_update_plan');
register_deactivation_hook(__FILE__, 'engtz_cerasis_freight_deactivate_hit_to_update_plan');
register_activation_hook(__FILE__, 'en_fdo_gtz_ltl_update_coupon_status_activate');
register_deactivation_hook(__FILE__, 'en_fdo_gtz_ltl_update_coupon_status_deactivate');
register_activation_hook(__FILE__, 'en_va_gtz_ltl_update_coupon_status_activate');
register_deactivation_hook(__FILE__, 'en_va_gtz_ltl_update_coupon_status_deactivate');
register_deactivation_hook(__FILE__, 'en_gtz_deactivate_plugin');
register_activation_hook(__FILE__, array('Engtz_Install_Uninstall', 'create_gtz_ltl_shipping_rules_db'));

add_action('admin_init', array('Engtz_Install_Uninstall', 'create_gtz_ltl_shipping_rules_db'));
add_action('woocommerce_shipping_init', 'en_gtz_shipping_method_init');
add_filter('woocommerce_shipping_methods', 'add_en_gtz_shipping_method');

/**
 * Cerasis plugin update now
 * @param array type $upgrader_object
 * @param array type $options
 */
if (!function_exists('engtz_cerasis_update_now')) {

    function engtz_cerasis_update_now()
    {
        $index = 'ltl-freight-quotes-globaltranz-edition/ltl-freight-quotes-globaltranz-edition.php';
        $plugin_info = get_plugins();
        $plugin_version = (isset($plugin_info[$index]['Version'])) ? $plugin_info[$index]['Version'] : '';
        $update_now = get_option('engtz_cerasis_update_now');

        if ($update_now != $plugin_version) {
            if (!function_exists('engtz_cerasis_freight_activate_hit_to_update_plan')) {
                require_once(__DIR__ . '/update-plan.php');
            }

            engtz_old_store_cerasis_ltl_dropship_status();
            engtz_old_store_cerasis_ltl_hazmat_status();
            engtz_cerasis_freight_activate_hit_to_update_plan();
            Engtz_Install_Uninstall::install();
            Engtz_Install_Uninstall::create_gtz_ltl_shipping_rules_db();

            update_option('engtz_cerasis_update_now', $plugin_version);
        }
    }

    add_action('init', 'engtz_cerasis_update_now');
}

/**
 * Add GlobalTranz Shipping Method
 * @param $methods
 */
if (!function_exists('add_en_gtz_shipping_method')) {
    function add_en_gtz_shipping_method($methods)
    {
        $methods['engtz_cerasis_shipping_method'] = 'Engtz_GlobalTranz_Shipping_Method';
        return $methods;
    }
}

define("engtz_cerasis_freights", "cerasis_freights");


/**
 * Load Frontend scripts for cerasis
 */
if (!function_exists('engtz_cerasis_frontend_checkout_script')) {

    function engtz_cerasis_frontend_checkout_script()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('engtz_cerasis_frontend_checkout_script', plugin_dir_url(__FILE__) . 'front/js/en-globaltranz-checkout.js', array(), '1.0.0');
        wp_localize_script('engtz_cerasis_frontend_checkout_script', 'frontend_script', array(
            'pluginsUrl' => plugins_url(),
        ));
    }

    add_action('wp_enqueue_scripts', 'engtz_cerasis_frontend_checkout_script');
}

/**
 * Weekly cron
 */
if (!function_exists('engtz_add_every_weekly_cron_get_carriers')) {

    function engtz_add_every_weekly_cron_get_carriers($schedules)
    {
        $schedules['every_three_minutes'] = array(
            'interval' => 60 * 60 * 24 * 7,
            'display' => __('Every Week', 'Cerasis Get Carriers')
        );
        return $schedules;
    }

    add_filter('cron_schedules', 'engtz_add_every_weekly_cron_get_carriers');
}
// Schedule an action if it's not already scheduled
if (!wp_next_scheduled('engtz_add_every_weekly_cron_get_carriers')) {
    wp_schedule_event(time(), 'every_three_minutes', 'engtz_add_every_weekly_cron_get_carriers');
}

// Hook into that action that'll fire every three minutes

if (!function_exists('engtz_every_weekly_event_func')) {

    function engtz_every_weekly_event_func()
    {
        include_once plugin_dir_path(__FILE__) . 'includes/carriers/en-globaltranz-carrier-list.php';
        $engtz_cerasis_carrier_list = new Engtz_Cerasis_Carrier_List();
        $engtz_cerasis_carrier_list->carriers();
    }

    add_action('engtz_add_every_weekly_cron_get_carriers', 'engtz_every_weekly_event_func');
}
/**
 * Plans Common Hooks
 */
if (!function_exists('globaltranz_quotes_plans_suscription_and_features')) {

    function globaltranz_quotes_plans_suscription_and_features($feature)
    {
        $package = get_option('cerasis_freight_package');

        $features = array
        (
            'instore_pickup_local_devlivery' => array('3'),
            'nested_material' => array('3'),
            'stackable_option' => array('', '0', '1', '2', '3'),
            'hazardous_material' => array('2', '3'),
            // Cuttoff Time
            'gt_cutt_off_time' => array('2', '3')
        );

        if (get_option('cerasis_freight_quotes_store_type') == "1") {
            $features['multi_warehouse'] = array('2', '3');
            $features['multi_dropship'] = array('', '0', '1', '2', '3');
            $features['hazardous_material'] = array('2', '3');
        } else {
            $dropship_status = get_option('en_old_user_dropship_status');
            $warehouse_status = get_option('en_old_user_warehouse_status');
            $hazmat_status = get_option('en_old_user_hazmat_status');

            isset($dropship_status) && ($dropship_status == "0") ? $features['multi_dropship'] = array('', '0', '1', '2', '3') : '';
            isset($warehouse_status) && ($warehouse_status == "0") ? $features['multi_warehouse'] = array('2', '3') : '';
            isset($hazmat_status) && ($hazmat_status == "1") ? $features['hazardous_material'] = array('2', '3') : '';
        }

        return (isset($features[$feature]) && (in_array($package, $features[$feature]))) ? TRUE : ((isset($features[$feature])) ? $features[$feature] : '');
    }

    add_filter('globaltranz_quotes_plans_suscription_and_features', 'globaltranz_quotes_plans_suscription_and_features', 1);
}


if (!function_exists('globaltranz_plans_notification_link')) {

    function globaltranz_plans_notification_link($plans)
    {
        $plan = current($plans);
        $plan_to_upgrade = "";
        switch ($plan) {
            case 2:
                $plan_to_upgrade = "<a target='_blank' class='plan_color' href='http://eniture.com/plan/woocommerce-cerasis-ltl-freight/'>Standard Plan required</a>";
                break;
            case 3:
                $plan_to_upgrade = "<a target='_blank' href='http://eniture.com/plan/woocommerce-cerasis-ltl-freight/'>Advanced Plan required</a>";
                break;
        }

        return $plan_to_upgrade;
    }

    add_filter('globaltranz_plans_notification_link', 'globaltranz_plans_notification_link', 1);
}

/**
 *
 * old customer check dropship / warehouse status on plugin update
 */
if (!function_exists('engtz_old_store_cerasis_ltl_dropship_status')) {

    function engtz_old_store_cerasis_ltl_dropship_status()
    {
        global $wpdb;

//  Check total no. of dropships on plugin updation
        $table_name = $wpdb->prefix . 'warehouse';
        $count_query = "select count(*) from $table_name where location = 'dropship' ";
        $num = $wpdb->get_var($count_query);

        if (get_option('en_old_user_dropship_status') == "0" && get_option('cerasis_freight_quotes_store_type') == "0") {
            $dropship_status = ($num > 1) ? 1 : 0;

            update_option('en_old_user_dropship_status', "$dropship_status");
        } elseif (get_option('en_old_user_dropship_status') == "" && get_option('cerasis_freight_quotes_store_type') == "0") {
            $dropship_status = ($num == 1) ? 0 : 1;

            update_option('en_old_user_dropship_status', "$dropship_status");
        }

//  Check total no. of warehouses on plugin updation
        $table_name = $wpdb->prefix . 'warehouse';
        $warehouse_count_query = "select count(*) from $table_name where location = 'warehouse' ";
        $warehouse_num = $wpdb->get_var($warehouse_count_query);

        if (get_option('en_old_user_warehouse_status') == "0" && get_option('cerasis_freight_quotes_store_type') == "0") {
            $warehouse_status = ($warehouse_num > 1) ? 1 : 0;

            update_option('en_old_user_warehouse_status', "$warehouse_status");
        } elseif (get_option('en_old_user_warehouse_status') == "" && get_option('cerasis_freight_quotes_store_type') == "0") {
            $warehouse_status = ($warehouse_num == 1) ? 0 : 1;

            update_option('en_old_user_warehouse_status', "$warehouse_status");
        }
    }

}

/**
 *
 * old customer check hazmat status on plugin update
 */
if (!function_exists('engtz_old_store_cerasis_ltl_hazmat_status')) {

    function engtz_old_store_cerasis_ltl_hazmat_status()
    {
        global $wpdb;

//  Check total no. of warehouses on plugin updation
        $results = $wpdb->get_results("SELECT meta_key FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '_hazardousmaterials%' AND meta_value = 'yes'
            "
        );

        if (get_option('en_old_user_hazmat_status') == "0" && get_option('cerasis_freight_quotes_store_type') == "0") {
            $hazmat_status = (count($results) > 0) ? 0 : 1;
            update_option('en_old_user_hazmat_status', "$hazmat_status");
        } elseif (get_option('en_old_user_hazmat_status') == "" && get_option('cerasis_freight_quotes_store_type') == "0") {
            $hazmat_status = (count($results) == 0) ? 1 : 0;
            update_option('en_old_user_hazmat_status', "$hazmat_status");
        }
    }

}
// use for load icon path
if (!defined('ENGTZ_DIRPATH')) {
    define('ENGTZ_DIRPATH', plugins_url('', __FILE__));
}

/**
 * Function that will trigger on activation
 */
function en_fdo_gtz_ltl_update_coupon_status_activate()
{
    $fdo_coupon_data = get_option('en_fdo_coupon_data');
    if(!empty($fdo_coupon_data)){
        $fdo_coupon_data_decorded = json_decode($fdo_coupon_data);
        if(isset($fdo_coupon_data_decorded->promo)){
            $data = array(
                'marketplace' => 'wp',
                'promocode' => $fdo_coupon_data_decorded->promo->coupon,
                'action' => 'install',
                'carrier' => 'GTZ'
            );
            $url = GT_FDO_COUPON_BASE_URL . "/change_promo_code_status";
            $response = wp_remote_get($url,
                array(
                    'method' => 'GET',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $data,
                )
            );
        }
    }
}
/**
 * Function that will trigger on deactivation
 */
function en_fdo_gtz_ltl_update_coupon_status_deactivate()
{
    $fdo_coupon_data = get_option('en_fdo_coupon_data');
    if(!empty($fdo_coupon_data)){
        $fdo_coupon_data_decorded = json_decode($fdo_coupon_data);
        if(isset($fdo_coupon_data_decorded->promo)){
            $data = array(
                'marketplace' => 'wp',
                'promocode' => $fdo_coupon_data_decorded->promo->coupon,
                'action' => 'uninstall',
                'carrier' => 'GTZ'
            );
            $url = GT_FDO_COUPON_BASE_URL . "/change_promo_code_status";
            $response = wp_remote_get($url,
                array(
                    'method' => 'GET',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $data,
                )
            );
        }
    }
}

/**
 * Function that will trigger on activation
 */
function en_va_gtz_ltl_update_coupon_status_activate()
{
    $va_coupon_data = get_option('en_va_coupon_data');
    if(!empty($va_coupon_data)){
        $va_coupon_data_decorded = json_decode($va_coupon_data);
        if(isset($va_coupon_data_decorded->promo)){
            $data = array(
                'marketplace' => 'wp',
                'promocode' => $va_coupon_data_decorded->promo->coupon,
                'action' => 'install',
                'carrier' => 'GTZ'
            );
            $url = GT_VA_COUPON_BASE_URL . "/change_promo_code_status?";
            $response = wp_remote_get($url,
                array(
                    'method' => 'GET',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $data,
                )
            );
        }
    }
}
/**
 * Function that will trigger on deactivation
 */
function en_va_gtz_ltl_update_coupon_status_deactivate()
{
    $va_coupon_data = get_option('en_va_coupon_data');
    if(!empty($va_coupon_data)){
        $va_coupon_data_decorded = json_decode($va_coupon_data);
        if(isset($va_coupon_data_decorded->promo)){
            $data = array(
                'marketplace' => 'wp',
                'promocode' => $va_coupon_data_decorded->promo->coupon,
                'action' => 'uninstall',
                'carrier' => 'GTZ'
            );
            $url = GT_VA_COUPON_BASE_URL . "/change_promo_code_status?";
            $response = wp_remote_get($url,
                array(
                    'method' => 'GET',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $data,
                )
            );
        }
    }
}

require_once 'fdo/en-coupon-api.php';
new EnGTZCouponAPI();

add_filter('en_suppress_parcel_rates_hook', 'supress_parcel_rates');
if (!function_exists('supress_parcel_rates')) {
    function supress_parcel_rates() {
        $exceedWeight = get_option('en_plugins_return_LTL_quotes') == 'yes';
        $supress_parcel_rates = get_option('en_suppress_parcel_rates') == 'suppress_parcel_rates';
        return ($exceedWeight && $supress_parcel_rates);
    }
}

/**
 * Remove Option For GTZ
*/
if (!function_exists('en_gtz_deactivate_plugin')) {
    function en_gtz_deactivate_plugin($network_wide = null)
    {
        if ( is_multisite() && $network_wide ) {
            foreach (get_sites(['fields'=>'ids']) as $blog_id) {
                switch_to_blog($blog_id);
                $eniture_plugins = get_option('EN_Plugins');
                $plugins_array = json_decode($eniture_plugins, true);
                $plugins_array = !empty($plugins_array) && is_array($plugins_array) ? $plugins_array : array();
                $key = array_search('engtz_cerasis_shipping_method', $plugins_array);
                if ($key !== false) {
                    unset($plugins_array[$key]);
                }
    
                update_option('EN_Plugins', json_encode($plugins_array));
                restore_current_blog();
            }
        } else {
            $eniture_plugins = get_option('EN_Plugins');
            $plugins_array = json_decode($eniture_plugins, true);
            $plugins_array = !empty($plugins_array) && is_array($plugins_array) ? $plugins_array : array();
            $key = array_search('engtz_cerasis_shipping_method', $plugins_array);
            if ($key !== false) {
                unset($plugins_array[$key]);
            }
    
            update_option('EN_Plugins', json_encode($plugins_array));
        }
    }
}