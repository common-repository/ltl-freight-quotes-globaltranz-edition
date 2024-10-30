<?php

/**
 * WWE Small Get Distance
 *
 * @package     WWE Small Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Distance Request Class
 */
if (!class_exists('Engtz_Get_cerasis_freight_distance')) {

    class Engtz_Get_cerasis_freight_distance
    {

        function __construct()
        {
            add_filter("engtz_wd_get_address", array($this, "sm_address"), 10, 2);
        }

        /**
         * Get Address Upon Access Level
         * @param $map_address
         * @param $accessLevel
         */
        function cerasis_freight_address($map_address, $accessLevel, $destinationZip = array())
        {
            $domain = engtz_cerasis_get_domain();
            $postData = array(
                'acessLevel' => $accessLevel,
                'address' => $map_address,
                'originAddresses' => (isset($map_address)) ? $map_address : "",
                'destinationAddress' => (isset($destinationZip)) ? $destinationZip : "",
                'eniureLicenceKey' => get_option('wc_settings_cerasis_licence_key'),
                'ServerName' => $domain,
            );
            $cerasis_Curl_Request = new Engtz_Curl_Class();
            $output = $cerasis_Curl_Request->get_curl_response(GT_HITTING_DOMAIN_URL . '/addon/google-location.php', $postData);
            return $output;
        }

    }

}