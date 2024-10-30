<?php

/**
 * Connection Request Class | getting connection
 * @package     Woocommerce GlobalTranz Edition
 * @author      <https://eniture.com/>
 * @version     v.1..0 (01/10/2017)
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Connection Request Class | getting connection with server
 */
if (!class_exists('Engtz_Connection_Request')) {

    class Engtz_Connection_Request {

        /**
         * cerasis connection request class constructor
         */
        function __construct() {
            add_action('wp_ajax_nopriv_test_connection_call', array($this, 'cerasis_test_connection'));
            add_action('wp_ajax_test_connection_call', array($this, 'cerasis_test_connection'));
        }

        /**
         * cerasis test connection function
         * @param none
         * @return array
         */
        function cerasis_test_connection() {
            if (isset($_POST)) {
                $data = wp_unslash($_POST);
                $shippingID = sanitize_text_field($data['wc_cerasis_shipper_id']);
                $username = sanitize_text_field($data['wc_cerasis_username']);
                $password = sanitize_text_field($data['wc_cerasis_password']);
                $accessKey = sanitize_text_field($data['authentication_key']);
                $license_key = sanitize_text_field($data['wc_cerasis_licence_key']);

                // Global tranz
                $wc_global_tranz_username = sanitize_text_field($data['wc_global_tranz_username']);
                $wc_global_tranz_password = $data['wc_global_tranz_password'];
                $wc_global_tranz_authentication_key = sanitize_text_field($data['wc_global_tranz_authentication_key']);
                $wc_global_tranz_customer_id = sanitize_text_field($data['wc_global_tranz_customer_id']);
                $cerasis_global_tranz_api_endpoint = sanitize_text_field($data['cerasis_global_tranz_api_endpoint']);

                // New API
                $gtz_new_api_client_id = sanitize_text_field($data['wc_gtz_new_api_client_id']);
                $gtz_new_api_client_secret = sanitize_text_field($data['wc_gtz_new_api_client_secret']);
                $gtz_new_api_username = sanitize_text_field($data['wc_gtz_new_api_username']);
                $gtz_new_api_password = $data['wc_gtz_new_api_password'];

                $domain = engtz_cerasis_get_domain();
                $post_data = [];
                switch ($cerasis_global_tranz_api_endpoint) {
                    case 'wc_global_tranz_api_fields':

                        $post_data = [
                            'username' => $wc_global_tranz_username,
                            'password' => $wc_global_tranz_password,
                            'accessKey' => $wc_global_tranz_authentication_key,
                            'customerId' => $wc_global_tranz_customer_id,
                            'carrierName' => 'globalTranz',
                        ];
                        break;
                    default:

                        $post_data = [
                            'shipperID' => $shippingID,
                            'username' => $username,
                            'password' => $password,
                            'accessKey' => $accessKey,
                            'carrierName' => 'cerasis',
                        ];
                        break;
                }

                $data = array(
                    'license_key' => $license_key,
                    'server_name' => $domain,
                    'platform' => 'WordPress',
                    'carrier_mode' => 'test',
                );

                $data = array_merge($data, $post_data);

                // New API test connection
                if ($cerasis_global_tranz_api_endpoint == 'wc_global_tranz_new_api_fields') {
                    $data = array(
                        'plugin_licence_key' => $license_key,
                        'plugin_domain_name' => $domain,
                        'clientId' => $gtz_new_api_client_id,
                        'clientSecret' => $gtz_new_api_client_secret,
                        'speed_freight_username' => $gtz_new_api_username,
                        'speed_freight_password' => $gtz_new_api_password,
                        'ApiVersion' => '2.0',
                        'isUnishipperNewApi' => 'yes',
                        'requestFromGlobalTranz' => '1'
                    );
                }
            }


            $api_endpoint = isset($_POST['cerasis_global_tranz_api_endpoint']) ? $_POST['cerasis_global_tranz_api_endpoint'] : '';
            $url = GT_HITTING_DOMAIN_URL . '/index.php';
            if ($api_endpoint == 'wc_global_tranz_new_api_fields') {
                $url = GT_NEW_API_HITTING_DOMAIN_URL . '/carriers/wwe-freight/speedfreightTest.php';
            }

            $cerasis_curl_obj = new Engtz_Curl_Class();
            $sResponseData = $cerasis_curl_obj->get_curl_response($url, $data);
            $output_decoded = json_decode($sResponseData);

            if (empty($output_decoded)) {
                $re['error'] = 'We are unable to test connection. Please try again later.';
            }
            if (isset($output_decoded->severity) && $output_decoded->severity == 'SUCCESS') {

                $re['success'] = $output_decoded->Message;
            } else if (isset($output_decoded->severity) && $output_decoded->severity == 'ERROR') {
                $re['error'] = $output_decoded->Message;
            } else if (isset($output_decoded->error) && isset($output_decoded->error_desc) && !empty($output_decoded->error_desc)) {
                $re['error'] = $output_decoded->error_desc;
            } else {

                $re['error'] = $output_decoded->error;
            }
            echo json_encode($re);
            exit();
        }

    }

}