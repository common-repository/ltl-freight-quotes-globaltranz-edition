<?php
$site_path = fs_get_wp_config_path();

function fs_get_wp_config_path()
{
    $base = dirname(__FILE__);
    $path = false;

    if (@file_exists(dirname(dirname($base)) . "/wp-config.php")) {
        $path = dirname(dirname($base));
    } else
        if (@file_exists(dirname(dirname(dirname($base))) . "/wp-config.php")) {
            $path = dirname(dirname(dirname($base)));
        } else
            $path = false;

    if ($path != false) {
        $path = str_replace("\\", "/", $path);
    }
    return $path;
}

require($site_path . '/wp-load.php');

$get_option = get_option('cerasis_freights_web_hook_plan_requests');
$engtz_web_hook_requests = (isset($get_option) && (!empty($get_option))) ? json_decode($get_option, TRUE) : array();
$engtz_web_hook_requests[] = (isset($_GET)) ? wp_unslash($_GET) : wp_unslash(array());
update_option('cerasis_freights_web_hook_plan_requests', json_encode($engtz_web_hook_requests));

$plan = isset($_GET['pakg_group']) ? sanitize_text_field($_GET['pakg_group']) : '';
if ($plan == '0' || $plan == '1' || $plan == '2' || $plan == '3') {
    if ($_GET['pakg_price'] == '0') {
        $plan = '0';
    }

    update_option('cerasis_freight_package', "$plan");

    $plan_type = isset($_GET['plan_type']) ? sanitize_text_field($_GET['plan_type']) : '';
    update_option("cerasis_freight_quotes_store_type", "$plan_type");

    $expire_days = isset($_GET['pakg_duration']) ? sanitize_text_field($_GET['pakg_duration']) : '';
    update_option('cerasis_freight_package_expire_days', "$expire_days");

    $expiry_date = isset($_GET['expiry_date']) ? sanitize_text_field($_GET['expiry_date']) : '';
    update_option('cerasis_freight_package_expire_date', "$expiry_date");

    engtz_check_cerasis_ltl_plan_on_product_detail();

}

