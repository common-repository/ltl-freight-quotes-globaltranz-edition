<?php

/**
 * Quote Request Class | quote request for getting carriers
 * @package     Woocommerce GlobalTranz Edition
 * @author      <https://eniture.com/>
 * @version     v.1..0 (01/10/2017)
 * @copyright   Copyright (c) 2017, Eniture
 */

use EnFreightviewResponse\EnFreightviewResponse;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Quote Request Class | getting request for cart items, sending request
 */
if (!class_exists('Engtz_Quotes_Request')) {

    class Engtz_Quotes_Request extends Engtz_Cart_To_Request
    {

        /**
         * details array
         * @var array type
         */
        public $quote_settings;
        public $instorepickup;
        public $en_wd_origin_array;

        /**
         * Quotes Request
         * @param $packages
         * @return array
         */
        public function quotes_request($packages, $package_plugin = '')
        {
            // FDO
            $engtz_global_tranz_fdo = new Engtz_Global_Tranz_Fdo();
            $en_fdo_meta_data = array();
            if (!empty($packages))
                $residential_detecion_flag = get_option("en_woo_addons_auto_residential_detecion_flag");

            $this->en_wd_origin_array = (isset($packages['origin'])) ? $packages['origin'] : array();

            $meta_data = [];
            $origins_markup = $products_markup = [];

            $hazardous_material = apply_filters('globaltranz_quotes_plans_suscription_and_features', 'hazardous_material');
            $hazmat_flag = false;
            if (isset($packages['origin'])) {
                foreach ($packages['origin'] as $key => $origin_details) {
                    $zip = $origin_details['zip'];
                    $item = (isset($packages['items'][$key])) ? $packages['items'][$key] : [];
                    $product_name = (isset($item['product_name'])) ? $item['product_name'] : '';
                    $sender_name = $origin_details['location'] . ": " . $origin_details['city'] . ", " . $origin_details['state'] . " " . $origin_details['zip'];
                    if (!isset($meta_data[$zip])) {
                        $meta_data[$zip]['origin'] = $origin_details;
                        $meta_data[$zip]['sender_origin'] = $sender_name;
                    }

                    $meta_data[$zip]['product_name'][] = $product_name;
                    $meta_data[$zip]['items'][] = $item;
                    if (!is_array($hazardous_material) && isset($item['isHazmatLineItem']) && $item['isHazmatLineItem'] == 'Y') {
                        $meta_data[$zip]['hazardous'][] = 'H'; 
                        $hazmat_flag = true;
                    }
                    
                    (isset($item['isStackableLineItem']) && $item['isStackableLineItem'] == 'Y') ? $meta_data[$zip]['stackable'][] = 'S' : '';

                    $origins_markup[$zip] = isset($origin_details['origin_markup']) ? $origin_details['origin_markup'] : 0;
                    $products_markup[$zip] = isset($packages['items'][$key]['markup']) ? $packages['items'][$key]['markup'] : 0;
                }

                foreach ($meta_data as $meta_zip => $meta_details) {
                    $en_fdo_meta_data = $engtz_global_tranz_fdo->engtz_cart_package($meta_details);
                    $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
                    $meta_data[$meta_zip]['en_fdo_meta_data'] = $en_fdo_meta_data;
                    $meta_data[$meta_zip]['en_fdo_meta_data'] = array_merge($meta_data[$meta_zip]['en_fdo_meta_data'], $engtz_global_tranz_fdo->engtz_package_hazardous($packages, $en_fdo_meta_data));
                }
            }

            // Check plan for nested material
            $nested_plan = apply_filters('globaltranz_quotes_plans_suscription_and_features', 'nested_material');
            $doNesting = "";
            foreach ($packages['items'] as $item) {
                $nestingPercentage[] = $item['nestedPercentage'];
                $nestedDimension[] = $item['nestedDimension'];
                $nestedItems[] = $item['nestedItems'];
                $stakingProperty[] = $item['stakingProperty'];
                isset($item['nestedMaterial']) && !empty($item['nestedMaterial']) &&
                $item['nestedMaterial'] == 'yes' && !is_array($nested_plan) ? $doNesting = 1 : "";
            }

            $domain = engtz_cerasis_get_domain();

            // Cuttoff Time
            $shipment_week_days = "";
            $order_cut_off_time = "";
            $shipment_off_set_days = "";
            $modify_shipment_date_time = "";
            $store_date_time = "";
            $gt_delivery_estimates = get_option('gt_delivery_estimates');
            $shipment_week_days = $this->gt_shipment_week_days();
            if ($gt_delivery_estimates == 'delivery_days' || $gt_delivery_estimates == 'delivery_date') {
                $order_cut_off_time = $this->quote_settings['orderCutoffTime'];
                $shipment_off_set_days = $this->quote_settings['shipmentOffsetDays'];
                $modify_shipment_date_time = ($order_cut_off_time != '' || $shipment_off_set_days != '' || (is_array($shipment_week_days) && count($shipment_week_days) > 0)) ? 1 : 0;
                $store_date_time = $today = date('Y-m-d H:i:s', current_time('timestamp'));
            }

            $cerasisLiftGateAsAnOption = false;
            $cerasis_global_tranz_shipping_service = get_option('cerasis_global_tranz_shipping_service');
            $cerasis_global_tranz_api_endpoint = get_option('cerasis_global_tranz_api_endpoint');
            
            // Liftgate exclude limit based on the liftgate weight restrictions shipping rule
            $shipping_rules_obj = new EnGtzLtlShippingRulesAjaxReq();
            $liftGateExcludeLimit = $shipping_rules_obj->get_liftgate_exclude_limit();
            
            if ($cerasis_global_tranz_api_endpoint == 'wc_global_tranz_api_fields') {
                $this->apiVersion = '2.0';
                $this->carrier_name = 'globalTranz';
                $globalTranz_request = [
                    'licenseKey' => get_option('wc_settings_cerasis_licence_key'),
                    'serverName' => $_SERVER['SERVER_NAME'],
                    'serverName' => $domain,
                    'carrierMode' => $this->carrierMode,
                    'quotestType' => $this->quotestType,
                    'version' => $this->version(),
                    'returnQuotesOnExceedWeight' => $this->quotes_on_exceed_weight(),
                    'api' => $this->api_credentials($packages, $doNesting, $modify_shipment_date_time, $order_cut_off_time, $shipment_off_set_days, $store_date_time, $shipment_week_days),
                    'originAddress' => $this->origin_address($packages),
                    'liftGateAsAnOption' => get_option('cerasis_freights_liftgate_delivery_as_option') == 'yes' ? '1' : '0',
                    'notifyAsAnOption' => get_option('cerasis_freights_notify_as_option') == 'yes' ? '1' : '0',
                ];

                if (!empty($liftGateExcludeLimit) && $liftGateExcludeLimit > 0) {
                    $globalTranz_request['api']['liftgateExcludeLimit'] = $liftGateExcludeLimit;
                }

                // Configure standard plugin with pallet packaging addon
                $globalTranz_request = apply_filters('en_pallet_identify', $globalTranz_request);

                $carriers = [
                    'globalTranz' => $globalTranz_request
                ];
            } else {
                $cerasis_request = [
                    'licenseKey' => get_option('wc_settings_cerasis_licence_key'),
                    'serverName' => $_SERVER['SERVER_NAME'],
                    'serverName' => $domain,
                    'carrierMode' => $this->carrierMode,
                    'quotestType' => $this->quotestType,
                    'version' => $this->version(),
                    'returnQuotesOnExceedWeight' => $this->quotes_on_exceed_weight(),
                    'api' => $this->api_credentials($packages, $doNesting, $modify_shipment_date_time, $order_cut_off_time, $shipment_off_set_days, $store_date_time, $shipment_week_days),
                    'originAddress' => $this->origin_address($packages),
                ];

                if (!empty($liftGateExcludeLimit) && $liftGateExcludeLimit > 0) {
                    $cerasis_request['api']['liftgateExcludeLimit'] = $liftGateExcludeLimit;
                }

                // Configure standard plugin with pallet packaging addon
                $cerasis_request = apply_filters('en_pallet_identify', $cerasis_request);

                $carriers = [
                    'cerasis' => $cerasis_request
                ];

                if (empty($cerasis_global_tranz_shipping_service) || $cerasis_global_tranz_shipping_service == 'wc_standard_lfq_service') {

                    // When liftgate fees added to carriers tab
                    $carr = $this->get_active_carriers();
                    $carrier_tab_liftgate = (isset($carr['carrier_tab_liftgate'])) ? $carr['carrier_tab_liftgate'] : false;
                    $cerasisLiftGateAsAnOption = true;
                    $carriers['cerasis']['liftGateAsAnOption'] = !$carrier_tab_liftgate && (get_option('cerasis_freights_liftgate_delivery_as_option') == 'yes') ? true : false;
                }
            }

            // Version numbers
            $plugin_versions = $this->en_version_numbers();
            $new_api_enabled = get_option('cerasis_global_tranz_api_endpoint') == 'wc_global_tranz_new_api_fields';
            
            // New API Request
            if ($new_api_enabled) {
                $this->apiVersion = '1.0.0';
                $this->carrier_name = 'wweLTL';

                $new_api_request = [
                    'licenseKey' => get_option('wc_settings_cerasis_licence_key'),
                    'serverName' => $domain,
                    'carrierMode' => $this->carrierMode,
                    'quotestType' => $this->quotestType,
                    'version' => $this->version(),
                    'returnQuotesOnExceedWeight' => $this->quotes_on_exceed_weight(),
                    'api' => $this->new_api_credentials($packages, $doNesting, $modify_shipment_date_time, $order_cut_off_time, $shipment_off_set_days, $store_date_time, $shipment_week_days, $hazmat_flag),
                    'getDistance' => '0',
                    'originAddress' => $this->origin_address($packages)
                ];

                if (!empty($liftGateExcludeLimit) && $liftGateExcludeLimit > 0) {
                    $new_api_request['api']['liftgateExcludeLimit'] = $liftGateExcludeLimit;
                }

                // Configure standard plugin with pallet packaging addon
                $new_api_request = apply_filters('en_pallet_identify', $new_api_request);

                $carriers = [
                    'wweLTL' => $new_api_request
                ];
            } 
            
            $post_data = array(
                // Version numbers
                'plugin_version' => $plugin_versions["en_current_plugin_version"],
                'wordpress_version' => get_bloginfo('version'),
                'woocommerce_version' => $plugin_versions["woocommerce_plugin_version"],

                'apiVersion' => $this->apiVersion,
                'plateform' => $this->plateform,
                'carrierName' => $this->carrier_name,
                'requestKey' => md5(microtime() . rand()),
                'suspend_residential' => get_option('suspend_automatic_detection_of_residential_addresses'),
                'residential_detecion_flag' => $residential_detecion_flag,
                'carriers' => $carriers,
                'receiverAddress' => $this->reciever_address(),
                'commdityDetails' => $this->line_items($packages['items']),
                // FDO
                'meta_data' => $meta_data,
                // Nested indexes
                'doNesting' => $doNesting,
                'nesting_percentage' => $nestingPercentage,
                'nesting_dimension' => $nestedDimension,
                'nested_max_limit' => $nestedItems,
                'nested_stack_property' => $stakingProperty,
                'origin_markup' => $origins_markup,
                'product_level_markup' => $products_markup
            );

            // Add these indexes for later use in applying shipping rules
            isset($packages['origin']) && $post_data['origin'] = $packages['origin'];
            isset($packages['product_tags']) && $post_data['product_tags'] = $packages['product_tags'];
            isset($packages['product_quantities']) && $post_data['product_quantities'] = $packages['product_quantities'];
            isset($packages['product_prices']) && $post_data['product_prices'] = $packages['product_prices'];
            isset($packages['shipment_weight']) && $post_data['shipment_weight'] = $packages['shipment_weight'];

            // Configure standard plugin with pallet packaging addon
            $post_data = apply_filters('en_pallet_identify', $post_data);

            if ($cerasis_global_tranz_shipping_service != 'wc_final_mile_service' || $cerasis_global_tranz_api_endpoint == 'wc_global_tranz_api_fields') {

                $post_data = apply_filters("en_woo_addons_carrier_service_quotes_request", $post_data, engtz_cerasis_freights);

                $post_data['receiverAddress']['addressLine2'] = isset($post_data['addressLine2']) ? $post_data['addressLine2'] : '';
                if ($cerasisLiftGateAsAnOption && isset($post_data['liftGateWithAutoResidentials']) && $post_data['liftGateWithAutoResidentials'] == '1' && $this->quote_settings['liftgate_delivery_option'] == 'yes' && $this->quote_settings['liftgate_resid_delivery'] == 'yes') {
                    unset($post_data['liftGateWithAutoResidentials']);
                }
            }

            // In-store pickup and local delivery
            $instore_pickup_local_devlivery_action = apply_filters('globaltranz_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');
            if (!is_array($instore_pickup_local_devlivery_action)) {
                if (isset($post_data['carriers'][$this->carrier_name]['originAddress'])) {
                    $engtz_wd_standard_plans = apply_filters('engtz_wd_standard_plans', $post_data, $post_data['receiverAddress']['receiverZip'], $this->en_wd_origin_array, $package_plugin);
                    foreach ($post_data['carriers'][$this->carrier_name]['originAddress'] as $origin_key => $origin_value) {
                        $post_data['carriers'][$this->carrier_name]['originAddress'][$origin_key]['InstorPickupLocalDelivery'] = $engtz_wd_standard_plans;
                    }
                }
            }

            // Eniture debug mood
            do_action("eniture_debug_mood", "Plugin Features(Cerasis) ", get_option('eniture_plugin_15'));
            do_action("eniture_debug_mood", "Quotes Request (Cerasis)", $post_data);

            // Error management
            $post_data = $this->applyErrorManagement($post_data);

            return $post_data;
        }

        /**
         * Return version numbers
         * @return int
         */
        function en_version_numbers()
        {
            if (!function_exists('get_plugins'))
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');

            $plugin_folder = get_plugins('/' . 'woocommerce');
            $plugin_file = 'woocommerce.php';
            $wc_plugin = (isset($plugin_folder[$plugin_file]['Version'])) ? $plugin_folder[$plugin_file]['Version'] : "";
            $get_plugin_data = get_plugin_data(GT_MAIN_FILE);
            $plugin_version = (isset($get_plugin_data['Version'])) ? $get_plugin_data['Version'] : '';

            $versions = array(
                "woocommerce_plugin_version" => $wc_plugin,
                "en_current_plugin_version" => $plugin_version
            );

            return $versions;
        }

        /**
         * @return shipment days of a week  - Cuttoff time
         */
        public function gt_shipment_week_days()
        {
            $shipment_days_of_week = array();

            if (get_option('all_shipment_days_gt') == 'yes') {
                return $shipment_days_of_week;
            }

            if (get_option('monday_shipment_day_gt') == 'yes') {
                $shipment_days_of_week[] = 1;
            }
            if (get_option('tuesday_shipment_day_gt') == 'yes') {
                $shipment_days_of_week[] = 2;
            }
            if (get_option('wednesday_shipment_day_gt') == 'yes') {
                $shipment_days_of_week[] = 3;
            }
            if (get_option('thursday_shipment_day_gt') == 'yes') {
                $shipment_days_of_week[] = 4;
            }
            if (get_option('friday_shipment_day_gt') == 'yes') {
                $shipment_days_of_week[] = 5;
            }

            return $shipment_days_of_week;
        }

        /**
         * Getting Line Items
         * @param $packages
         * @return array
         */
        function line_items($packages)
        {
            $line_item = array();
            $hazmat_flage = false;
            $hazardous_material = apply_filters('globaltranz_quotes_plans_suscription_and_features', 'hazardous_material');
            if (!is_array($hazardous_material)) {
                $hazmat_flage = true;
            }

            $counter = 0;
            foreach ($packages as $item) {
                // Standard Packaging
                $ship_as_own_pallet = isset($item['ship_as_own_pallet']) && $item['ship_as_own_pallet'] == 'yes' ? 1 : 0;
                $vertical_rotation_for_pallet = isset($item['vertical_rotation_for_pallet']) && $item['vertical_rotation_for_pallet'] == 'yes' ? 1 : 0;
                $counter = (isset($item['variantId']) && $item['variantId'] > 0) ? $item['variantId'] : $item['productId'];
                $nmfc_num = (isset($item['nmfc_number'])) ? $item['nmfc_number'] : '';
                $line_item[$counter] = array(
                    'product_id' => $counter,
                    'freightClass' => $item['freightClass'],
                    'lineItemHeight' => $item['productHeight'],
                    'lineItemLength' => $item['productLength'],
                    'lineItemWidth' => $item['productWidth'],
                    'lineItemClass' => $item['productClass'],
                    'lineItemWeight' => $item['productWeight'],
                    'piecesOfLineItem' => $item['productQty'],
                    'isHazmatLineItem' => isset($hazmat_flage) && ($hazmat_flage == true) ? $item['isHazmatLineItem'] : 'N',
                    'isStackableLineItem' => $item['isStackableLineItem'],
                    'lineItemNMFC' => $nmfc_num,
                    // Nesting
                    'nestedMaterial' => $item['nestedMaterial'],
                    'nestingPercentage' => $item['nestedPercentage'],
                    'nestingDimension' => $item['nestedDimension'],
                    'nestedLimit' => $item['nestedItems'],
                    'nestedStackProperty' => $item['stakingProperty'],
                    // Shippable handling units
                    'isPalletLineItem' => $item['isPalletLineItem'],
                    'lineItemPalletFlag' => $item['lineItemPalletFlag'],
                    'lineItemPackageType' => $item['lineItemPackageType'],
                    // Standard Packaging
                    'shipPalletAlone' => $ship_as_own_pallet,
                    'vertical_rotation' => $vertical_rotation_for_pallet,
                    'lineItemDescription' => $item['productName'],
                );
                $line_item[$counter] = apply_filters('en_fdo_carrier_service', $line_item[$counter], $item);
            }
            return $line_item;
        }

        /**
         * Checking item is hazmet or not
         * @param $packages
         * @return string
         */
        function item_hazmet($packages)
        {
            foreach ($packages['items'] as $item):
                $items_id[] = array(
                    'id' => $item['productId']
                );
            endforeach;
            foreach ($items_id as $pid):
                $enable_hazmet[] = get_post_meta($pid['id'], '_hazardousmaterials', true);
            endforeach;
            $hazmet = 'N';
            if (get_option('cerasis_freight_quotes_store_type') == "1") {
                $hazardous_material = apply_filters('globaltranz_quotes_plans_suscription_and_features', 'hazardous_material');
                if (!is_array($hazardous_material)) {
                    if (in_array("yes", $enable_hazmet)) {
                        $hazmet = 'Y';
                    } else {
                        $hazmet = 'N';
                    }
                }
            } else {
                if (in_array("yes", $enable_hazmet)) {
                    $hazmet = 'Y';
                } else {
                    $hazmet = 'N';
                }
            }
            return $hazmet;
        }

        /**
         * Checking item is hazmet or not
         * @param $packages
         * @return string
         */
        function item_stackable($packages)
        {
            foreach ($packages['items'] as $item):
                $items_id[] = array(
                    'id' => $item['productId']
                );
            endforeach;
            foreach ($items_id as $pid):
                $enable_stackable[] = get_post_meta($pid['id'], '_stackable', true);
            endforeach;
            $stackable = 'N';
            if (get_option('cerasis_freight_quotes_store_type') == "1") {
                $stackable_option = apply_filters('globaltranz_quotes_plans_suscription_and_features', 'stackable_option');
                if (!is_array($stackable_option)) {
                    if (in_array("yes", $enable_stackable)) {
                        $stackable = 'Y';
                    } else {
                        $stackable = 'N';
                    }
                }
            } else {
                if (in_array("yes", $enable_stackable)) {
                    $stackable = 'Y';
                } else {
                    $stackable = 'N';
                }
            }
            return $stackable;
        }

        /**
         * Checking item delivery is Residential/Liftgate
         * @return array
         */
        function item_accessorial()
        {
            $accessorials = array();
            $wc_liftgate = get_option('wc_settings_cerasis_lift_gate_delivery');
            $wc_offer_liftgate = get_option('cerasis_freights_liftgate_delivery_as_option');
            $wc_residential = get_option('wc_settings_cerasis_residential_delivery');

            $cerasis_global_tranz_api_endpoint = get_option('cerasis_global_tranz_api_endpoint');
            if ($cerasis_global_tranz_api_endpoint == 'wc_global_tranz_api_fields') {
                // Notify delivery
                $wc_notify = get_option('wc_settings_cerasis_notify');
                $wc_notify_option = get_option('cerasis_freights_notify_as_option');
                ($wc_liftgate == 'yes') ? $accessorials['LGD'] = '12' : "";
                ($wc_offer_liftgate == 'yes') ? $accessorials['LGD'] = '12' : "";
                ($wc_residential == 'yes') ? $accessorials['RESD'] = '14' : "";
                // Notify delivery
                ($wc_notify == 'yes') ? $accessorials['NBD'] = '17' : "";
                ($wc_notify_option == 'yes') ? $accessorials['NBD'] = '17' : "";
            } else {
                ($wc_liftgate == 'yes') ? $accessorials['LFTGATDEST'] = 'LFTGATDEST' : "";
                ($wc_residential == 'yes') ? $accessorials['RESDEL'] = 'RESDEL' : "";
            }

            return $accessorials;
        }

        /**
         * Getting origin address
         * @param $packages
         * @return array
         */
        function origin_address($packages)
        {
            $cerasis_global_tranz_api_endpoint = get_option('cerasis_global_tranz_api_endpoint');
            foreach ($packages['origin'] as $k => $origin):
                $origin['senderZip'] = preg_replace('/\s+/', '', $origin['zip']);
                if (trim($origin['country']) == 'USA') {
                    $origin['country'] = 'US';
                }

                $origin_address[$k] = array(
                    'locationId' => $origin['zip'],
                    'senderCity' => $origin['city'],
                    'senderState' => $origin['state'],
                    'senderZip' => $origin['zip'],
                    'location' => $origin['location'],
                    'senderCountryCode' => $this->origin_country($origin['country']),
                );
            endforeach;

            return $origin_address;
        }

        /**
         * Country Code
         * @param $country
         */
        function origin_country($country)
        {
            if (isset($country)) {
                $cerasis_global_tranz_api_endpoint = get_option('cerasis_global_tranz_api_endpoint');
                if ($cerasis_global_tranz_api_endpoint != 'wc_global_tranz_api_fields') {
                    if ($country == 'USA') {
                        $sender_country = "US";
                    } else if ($country == 'CAN' || $country == 'CN') {
                        $sender_country = 'CA';
                    }else{
                        $sender_country = $country;
                    }
                } else {
                    if ($country == 'US') {
                        $sender_country = "USA";
                    }elseif ($country == 'CA' || $country == 'CN'){
                        $sender_country = "CAN";
                    }else{
                        $sender_country = $country;
                    }
                }
                return $sender_country;
            }else{
                return $country;
            }
        }

        /**
         * Getting customer address
         * @return array
         */
        function reciever_address()
        {
            $billing_obj = new Engtz_Billing_Details();
            $billing_details = $billing_obj->billing_details();
            $freight_zipcode = "";
            $freight_state = "";
            $freight_city = "N";

            $cerasis_global_tranz_shipping_service = get_option('cerasis_global_tranz_shipping_service');

            (strlen(WC()->customer->get_shipping_postcode()) > 0) ? $freight_zipcode = WC()->customer->get_shipping_postcode() : $freight_zipcode = $billing_details['postcode'];
            (strlen(WC()->customer->get_shipping_state()) > 0) ? $freight_state = WC()->customer->get_shipping_state() : $freight_state = $billing_details['state'];
            (strlen(WC()->customer->get_shipping_country()) > 0) ? $freight_country = WC()->customer->get_shipping_country() : $freight_country = $billing_details['country'];
            (strlen(WC()->customer->get_shipping_city()) > 0) ? $freight_city = WC()->customer->get_shipping_city() : $freight_city = $billing_details['city'];
            (strlen(WC()->customer->get_shipping_address_1()) > 0) ? $freight_addressline = WC()->customer->get_shipping_address_1() : $freight_addressline = $billing_details['s_address'];

            if (trim($freight_country) == 'USA') {
                $freight_country = 'US';
            }
            $freight_zipcode = preg_replace('/\s+/', '', $freight_zipcode);
            $en_default_unconfirmed_address_types_to = get_option('en_default_unconfirmed_address_types_to');
            $address = array(
                'receiverCity' => $freight_city,
                'receiverState' => $freight_state,
                'receiverZip' => $freight_zipcode,
                'receiverCountryCode' => $this->origin_country($freight_country),
                'addressLine' => (isset($_POST['s_addres'])) ? wp_unslash($_POST['s_addres']) : $freight_addressline,
            );

            if (empty($cerasis_global_tranz_shipping_service) || $cerasis_global_tranz_shipping_service == 'wc_standard_lfq_service') {
                $address['defaultRADAddressType'] = ($en_default_unconfirmed_address_types_to && strlen($en_default_unconfirmed_address_types_to) > 0) ? $en_default_unconfirmed_address_types_to : 'residential';
            }

            return $address;
        }

        /**
         * API Credentials
         * @param $packages
         * @return array/string
         */
        function api_credentials($packages, $doNesting, $modify_shipment_date_time, $order_cut_off_time, $shipment_off_set_days, $store_date_time, $shipment_week_days)
        {
            $cerasis_global_tranz_api_endpoint = get_option('cerasis_global_tranz_api_endpoint');
            $cerasis_global_tranz_shipping_service = get_option('cerasis_global_tranz_shipping_service');
            $gt_rating_status = get_option('service_global_tranz_rating_method_ch_unch');
            if ($cerasis_global_tranz_api_endpoint == 'wc_global_tranz_api_fields') {
                $credentials = [
                    'username' => get_option('wc_settings_global_tranz_username'),
                    'password' => get_option('wc_settings_global_tranz_password'),
                    'accessKey' => get_option('wc_settings_global_tranz_authentication_key'),
                    'customerId' => get_option('wc_settings_global_tranz_customer_id'),
                    'direction' => 'Dropship',
                    'billingType' => 'Prepaid',
                    'doNesting' => $doNesting,
                    'accessorial' => $this->item_accessorial(),
                ];
                if ($gt_rating_status == 'yes') {
                    $credentials['accessLevel'] = 'pro';
                    $credentials['version'] = 2.0;
                }

            } elseif ($cerasis_global_tranz_api_endpoint == 'wc_cerasis_api_fields' && (empty($cerasis_global_tranz_shipping_service) || $cerasis_global_tranz_shipping_service == 'wc_standard_lfq_service')) {
                $credentials = array(
                    'shipperID' => get_option('wc_settings_cerasis_shipper_id'),
                    'username' => get_option('wc_settings_cerasis_username'),
                    'password' => get_option('wc_settings_cerasis_password'),
                    'accessKey' => get_option('wc_settings_cerasis_authentication_key'),
                    'direction' => 'Dropship',
                    'billingType' => 'Prepaid',
                    'doNesting' => $doNesting,
                    'hazmat' => $this->item_hazmet($packages),
                    'isStackableLineItem' => $this->item_stackable($packages),
                    'accessorial' => $this->item_accessorial(),
                );

            } elseif ($cerasis_global_tranz_shipping_service == 'wc_final_mile_service') {
                $credentials = array(
                    'shipperID' => get_option('wc_settings_cerasis_shipper_id'),
                    'username' => get_option('wc_settings_cerasis_username'),
                    'password' => get_option('wc_settings_cerasis_password'),
                    'accessKey' => get_option('wc_settings_cerasis_authentication_key'),
                    'direction' => 'Dropship',
                    'billingType' => 'Prepaid',
                    'isFinalMile' => '1',
                    'doNesting' => $doNesting,
                    'finalMileService' => strlen(trim($this->quote_settings['fm_services'])) > 0 ? $this->quote_settings['fm_services'] : 'THRSHLD_FM',
                );
            }

            $credentials['handlingUnitWeight'] = get_option('engtz_freight_handling_weight');
            $credentials['maxWeightPerHandlingUnit'] = get_option('engtz_freight_maximum_handling_weight');
            // Cuttoff Time
            $credentials['modifyShipmentDateTime'] = $modify_shipment_date_time;
            $credentials['OrderCutoffTime'] = $order_cut_off_time;
            $credentials['shipmentOffsetDays'] = $shipment_off_set_days;
            $credentials['storeDateTime'] = $store_date_time;
            $credentials['shipmentWeekDays'] = $shipment_week_days;
            return $credentials;
        }

        /**
         * Quotes On Exceed Weight
         * @return int
         */
        function quotes_on_exceed_weight()
        {
            $engtz_triggered = apply_filters('engtz_triggered', false);
            if ($engtz_triggered) {
                $quotes_on_exceed_weight = "10";
            } else {
                $quotes_on_exceed_weight = "0";
            }

            return $quotes_on_exceed_weight;
        }

        /**
         * getting quotes via curl class
         * @param $request_data
         * @return string
         */
        function get_quotes($request_data)
        {
            // check response from session
            $srequest_data = $request_data;
            $srequest_data['requestKey'] = "";
            $currentData = md5(json_encode($srequest_data));
            $requestFromSession = WC()->session->get('previousRequestData');

            $requestFromSession = ((is_array($requestFromSession)) && (!empty($requestFromSession))) ? $requestFromSession : array();
            if (isset($requestFromSession[$currentData]) && (!empty($requestFromSession[$currentData]))) {
                //Eniture debug mode
                do_action("eniture_debug_mood", "Build Query (CERASIS)", http_build_query($request_data));
                //Eniture debug mode
                do_action("eniture_debug_mood", "Quotes Response (CERASIS)", json_decode($requestFromSession[$currentData]));

                $resp_decorder_arr = json_decode($requestFromSession[$currentData], true);
                $instorepickup_resp = is_array($resp_decorder_arr) ? reset($resp_decorder_arr) : [];
                $instorepickup_resp = (is_array($instorepickup_resp) && !empty($instorepickup_resp)) ? reset($instorepickup_resp) : [];
                $this->instorepickup = isset($instorepickup_resp['InstorPickupLocalDelivery']) && !empty($instorepickup_resp['InstorPickupLocalDelivery']) ? $instorepickup_resp['InstorPickupLocalDelivery'] : array();

                $quote_response = json_decode($requestFromSession[$currentData]);
                
                if (isset($quote_response->cerasis) && !empty($quote_response->cerasis)) {
                    return $quote_response->cerasis;
                } else if (isset($quote_response->globalTranz) && !empty($quote_response->globalTranz)) {
                    return $quote_response->globalTranz;
                } else if (isset($quote_response->wweLTL) && !empty($quote_response->wweLTL)) {
                    return $quote_response->wweLTL;
                }
                return FALSE;
            }

            if (is_array($request_data) && count($request_data) > 0) {
                $curl_obj = new Engtz_Curl_Class();
                $request_data['requestKey'] = md5(microtime() . rand());

                $output = $curl_obj->get_curl_response($this->end_point_url_pro, $request_data);

//          set response in session
                $response = isset($output) && !empty($output) ? json_decode($output, TRUE) : array();
                //globalTranz
                if ((isset($response['cerasis']) && (!empty($response['cerasis']))) || (isset($response['globalTranz']) && (!empty($response['globalTranz']))) || (isset($response['wweLTL']) && (!empty($response['wweLTL'])))) {

                    if (isset($response['cerasis'])) {
                        $cerasis_reset = is_array($response['cerasis']) ? reset($response['cerasis']) : [];
                    } elseif (isset($response['globalTranz'])) {
                        $cerasis_reset = is_array($response['globalTranz']) ? reset($response['globalTranz']) : [];
                    } elseif (isset($response['wweLTL'])) {
                        $cerasis_reset = is_array($response['wweLTL']) ? reset($response['wweLTL']) : [];
                    }

                    if (isset($cerasis_reset['autoResidentialSubscriptionExpired']) &&
                        ($cerasis_reset['autoResidentialSubscriptionExpired'] == 1)) {
                        $flag_api_response = "no";
                        $srequest_data['residential_detecion_flag'] = $flag_api_response;
                        $currentData = md5(json_encode($srequest_data));
                    }
                    if (!isset($cerasis_reset['severity']) || (isset($cerasis_reset['severity']) && ($cerasis_reset['severity'] != "ERROR"))) {
                        $requestFromSession[$currentData] = $output;
                        WC()->session->set('previousRequestData', $requestFromSession);
                    }
                }

//          Eniture debug mood
                do_action("eniture_debug_mood", "Quotes Response (CERASIS)", json_decode($output));

                $quote_response = json_decode($output);

                $instorepickup_resp = (!empty($response) && is_array($response)) ? reset($response) : '';
                $instorepickup_resp = (!empty($instorepickup_resp) && is_array($instorepickup_resp)) ? reset($instorepickup_resp) : '';
                $this->instorepickup = isset($instorepickup_resp['InstorPickupLocalDelivery']) && !empty($instorepickup_resp['InstorPickupLocalDelivery']) ? $instorepickup_resp['InstorPickupLocalDelivery'] : array();

                if (isset($quote_response->cerasis) && !empty($quote_response->cerasis)) {
                    foreach ($quote_response->cerasis as $key => $value) {
                        if (isset($value->backupRate) && $value->backupRate == 1) {
                            return ['error' => 'backup_rate'];
                        }

                        if (isset($value->severity)) {
                            if ($value->severity == 'ERROR') {
                                return [];
                            }
                        }
                    }
                    return $quote_response->cerasis;
                } else if (isset($quote_response->globalTranz) && !empty($quote_response->globalTranz)) {
                    foreach ($quote_response->globalTranz as $key => $value) {
                        if (isset($value->backupRate) && $value->backupRate == 1) {
                            return ['error' => 'backup_rate'];
                        }

                        if (isset($value->severity)) {
                            if ($value->severity == 'ERROR') {
                                return [];
                            }
                        }
                    }
                    return $quote_response->globalTranz;
                } else if (isset($quote_response->wweLTL) && !empty($quote_response->wweLTL)) {
                    foreach ($quote_response->wweLTL as $key => $value) {
                        if (isset($value->backupRate) && $value->backupRate == 1) {
                            return ['error' => 'backup_rate'];
                        }

                        if (isset($value->severity)) {
                            if ($value->severity == 'ERROR') {
                                return [];
                            }
                        }
                    }
                    return $quote_response->wweLTL;
                }

                return FALSE;
            }
        }

        public function return_cerasis_localdelivery_array()
        {
            return json_decode(json_encode($this->instorepickup));
        }

        /**
         * check "R" in array
         * @param array type $label_sufex
         * @return array type
         */
        public function label_R_cerasis($label_sufex)
        {
            if (get_option('wc_settings_cerasis_residential_delivery') == 'yes' && (in_array("R", $label_sufex))) {
                $label_sufex = array_flip($label_sufex);
                unset($label_sufex['R']);
                $label_sufex = array_keys($label_sufex);
            }

            return $label_sufex;
        }

        /**
         * passing quotes result to display
         * @param $quotes
         * @param $cart_obj
         * @param $handlng_fee
         * @return string/array
         */
        function pass_quotes($quotes, $cart_obj, $handlng_fee, $request_data)
        {
            // FDO
            $en_fdo_meta_data = (isset($request_data['en_fdo_meta_data'])) ? $request_data['en_fdo_meta_data'] : '';
            if (isset($quotes['debug'])) {
                $en_fdo_meta_data['handling_unit_details'] = $quotes['debug'];
            }
            $standard_packaging = isset($quotes['standardPackagingData']) ? $quotes['standardPackagingData'] : [];

            $accessorials = [];

            ($this->quote_settings['liftgate_delivery'] == "yes") ? $accessorials[] = "L" : "";
            ($this->quote_settings['residential_delivery'] == "yes") ? $accessorials[] = "R" : "";
            (isset($request_data['hazardous']) && is_array($request_data['hazardous']) && in_array('H', $request_data['hazardous'])) ? $accessorials[] = "H" : "";
            (isset($request_data['stackable']) && is_array($request_data['stackable']) && in_array('S', $request_data['stackable'])) ? $accessorials[] = "S" : "";

            if (isset($en_fdo_meta_data['accessorials'])) {
                $en_fdo_meta_data['accessorials']['hazmat'] = in_array('H', $accessorials) ? true : false;
            }
            
            $meta_data['accessorials'] = json_encode($accessorials);
            $meta_data['sender_origin'] = $request_data['sender_origin'];
            $meta_data['product_name'] = json_encode($request_data['product_name']);
            $meta_data['standard_packaging'] = wp_json_encode($standard_packaging);

            $final_mile_label = '';
            switch ($this->quote_settings['fm_services']) {
                case 'PREMIUM_FM':
                    $final_mile_label = get_option('en_cerasis_premium_label');
                    break;
                case 'ROOMCHC_FM':
                    $final_mile_label = get_option('en_cerasis_room_of_choice_label');
                    break;
                case 'THRSHLD_FM':
                    $final_mile_label = get_option('en_cerasis_threshold_label');
                    break;
            }
            $cerasis_global_tranz_shipping_service = get_option('cerasis_global_tranz_shipping_service');
            $carr = $this->get_active_carriers();
            $allServices = array();
            $global_tranz_service = [
                'LowestCostRate' => [
                    'service' => get_option('service_global_tranz_lowest_cost_rate_quotes'),
                    'label' => 'Lowest Cost Rate'
                ],
                'QuickestTransitRate' => [
                    'service' => get_option('service_global_tranz_quickest_transit_rate_quotes'),
                    'label' => 'Quickest Transit Rate',
                ],
            ];
            if (isset($quotes)) {
                $Engtz_Liftgate_As_Option = new Engtz_Liftgate_As_Option();
                $label_sufex = $Engtz_Liftgate_As_Option->filter_label_sufex_array_cerasis_freights($quotes);

                if ($this->quote_settings['liftgate_delivery'] == "yes") {
                    foreach ($label_sufex as $key => $label_sufix_ind) {
                        if ($label_sufix_ind == 'L') {
                            unset($label_sufex[$key]);
                        }
                    }
                }

                $count = 0;
                $price_sorted_key = [];
                $parent_price_sorted_key = [];
                $simple_quotes = array();
                $quotesWithLiftGate = (isset($quotes['quotesWithLiftGate'])) ? $quotes['quotesWithLiftGate'] : [];
                $origin_level_markup = isset($quotes['origin_markup']) ? $quotes['origin_markup'] : 0;
                $product_level_markup = isset($quotes['product_level_markup']) ? $quotes['product_level_markup'] : 0;
                $quotes = (isset($quotes['q'])) ? $quotes['q'] : [];

                $cerasis_global_tranz_api_endpoint = get_option('cerasis_global_tranz_api_endpoint');

                if ($cerasis_global_tranz_api_endpoint == 'wc_global_tranz_api_fields') {
                    return $quotes;
                } else if (!empty($quotes)) {
                    $duplicate_label_sufex = $label_sufex;
                    foreach ($quotes as $key => $quote) {
                        if (isset($quote['CarrierScac'], $carr[$quote['CarrierScac']])) {
                            $carrier_tab_liftgate = (isset($carr['carrier_tab_liftgate'])) ? $carr['carrier_tab_liftgate'] : false;
                            if ((!empty($quotesWithLiftGate) && in_array('L', $duplicate_label_sufex)) || (!$carrier_tab_liftgate && empty($quotesWithLiftGate) && in_array('L', $duplicate_label_sufex) && $this->quote_settings['liftgate_delivery_option'] == "yes")) {
                                $flipped_label_sufex = array_flip($label_sufex);
                                if (isset($flipped_label_sufex['L'])) {
                                    unset($flipped_label_sufex['L']);
                                }

                                $label_sufex = array_flip($flipped_label_sufex);
                            }

                            $cost = $this->addOriginAndProductMarkup($quote, $product_level_markup, $origin_level_markup);

                            $allServices[$count] = array(
                                'id' => (isset($quote['CarrierScac'])) ? $quote['CarrierScac'] : '',
                                'carrier_scac' => (isset($quote['CarrierScac'])) ? $quote['CarrierScac'] : '',
                                'carrier_name' => (isset($quote['CarrierName'])) ? $quote['CarrierName'] : '',
                                'label' => (isset($quote['CarrierName'])) ? $quote['CarrierName'] : '',
                                'label_sufex' => $label_sufex,
                                'cost' => $cost,
                                // Cuttoff Time
                                'delivery_estimates' => $quote['totalTransitTimeInDays'],
                                'delivery_time_stamp' => $quote['deliveryDate'],
                                'meta_data' => $meta_data,
                                'plugin_name' => 'Cerasis',
                                'plugin_type' => 'ltl',
                                'owned_by' => 'eniture'
                            );

                            // FDO
                            $en_fdo_meta_data['rate'] = $allServices[$count];
                            if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                                unset($en_fdo_meta_data['rate']['meta_data']);
                            }

                            $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
                            $allServices[$count]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

                            if ($cerasis_global_tranz_shipping_service == 'wc_final_mile_service') {
                                strlen($final_mile_label) > 0 ? $allServices[$count]['label'] = $final_mile_label : '';
                            }

                            $allServices[$count] = apply_filters("en_woo_addons_web_quotes", $allServices[$count], engtz_cerasis_freights);

                            $label_sufex = (isset($allServices[$count]['label_sufex'])) ? $allServices[$count]['label_sufex'] : [];

                            $label_sufex = $this->label_R_cerasis($label_sufex);

                            in_array('R', $label_sufex) ? $allServices[$count]['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = true : '';
                            ($this->quote_settings['liftgate_resid_delivery'] == "yes") && (in_array("R", $label_sufex)) && in_array('L', $label_sufex) ? $allServices[$count]['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true : '';

                            if (!empty($quotesWithLiftGate) && isset($allServices[$count]) && $this->quote_settings['liftgate_resid_delivery'] == "yes" && in_array("R", $label_sufex)) {
                                unset($allServices[$count]);
                            } else {
                                $allServices[$count]['label_sufex'] = $label_sufex;
                                $parent_price_sorted_key[$count] = (isset($allServices[$count]['cost'])) ? $allServices[$count]['cost'] : 0;
                            }

                            // When liftgate fees added to carriers tab
                            $liftgate_charge = (isset($carr[$quote['CarrierScac']])) ? $carr[$quote['CarrierScac']] : 0;
                            if (($carrier_tab_liftgate && empty($quotesWithLiftGate)) && ($this->quote_settings['liftgate_delivery_option'] == "yes") && array_filter($carr) &&
                                (($this->quote_settings['liftgate_resid_delivery'] == "yes") && (!in_array("R", $label_sufex)) ||
                                    ($this->quote_settings['liftgate_resid_delivery'] != "yes"))) {
                                if ($liftgate_charge > 0) {
                                    $service = $allServices[$count];
                                    (isset($service['id'])) ? $service['id'] .= "WL" : $service['id'] = "WL";

                                    (isset($service['label_sufex']) &&
                                        (!empty($service['label_sufex']))) ?
                                        array_push($service['label_sufex'], "L") :  // IF
                                        $service['label_sufex'] = array("L");       // ELSE
                                    $service['append_label'] = " with lift gate delivery ";
                                    $service['cost'] = (isset($service['cost'])) ? $service['cost'] + $liftgate_charge : 0;
                                    $simple_quotes[$count] = $service;

                                    // FDO
                                    if (isset($simple_quotes[$count]['meta_data']['en_fdo_meta_data']['rate']['cost'])) {
                                        $simple_quotes[$count]['meta_data']['en_fdo_meta_data']['rate']['cost'] = $service['cost'];
                                        $simple_quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true;
                                    }

                                    $price_sorted_key[$count] = (isset($simple_quotes[$count]['cost'])) ? $simple_quotes[$count]['cost'] : 0;
                                }

                            }

                            $count++;
                        }
                    }

                    if (!empty($quotesWithLiftGate) && ($this->quote_settings['liftgate_delivery_option'] == "yes")) {
                        $count = 0;

                        foreach ($quotesWithLiftGate as $key => $quote) {
                            if (isset($quote['CarrierScac'], $carr[$quote['CarrierScac']])) {
    
                                $cost = $this->addOriginAndProductMarkup($quote, $product_level_markup, $origin_level_markup);

                                $simple_quotes[$count] = array(
                                    'id' => (isset($quote['CarrierScac'])) ? $quote['CarrierScac'] . '_WL' : '',
                                    'carrier_scac' => (isset($quote['CarrierScac'])) ? $quote['CarrierScac'] : '',
                                    'carrier_name' => (isset($quote['CarrierName'])) ? $quote['CarrierName'] : '',
                                    'label' => (isset($quote['CarrierName'])) ? $quote['CarrierName'] : '',
                                    'label_sufex' => $label_sufex,
                                    'cost' => $cost,
                                    // Cuttoff Time
                                    'delivery_estimates' => $quote['totalTransitTimeInDays'],
                                    'delivery_time_stamp' => $quote['deliveryDate'],
                                    'meta_data' => $meta_data,
                                    'plugin_name' => 'Cerasis',
                                    'plugin_type' => 'ltl',
                                    'owned_by' => 'eniture'
                                );

                                // FDO
                                $en_fdo_meta_data['rate'] = $simple_quotes[$count];
                                if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                                    unset($en_fdo_meta_data['rate']['meta_data']);
                                }

                                $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
                                $simple_quotes[$count]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

                                $simple_quotes[$count] = apply_filters("en_woo_addons_web_quotes", $simple_quotes[$count], engtz_cerasis_freights);

                                $label_sufex = (isset($simple_quotes[$count]['label_sufex'])) ? $simple_quotes[$count]['label_sufex'] : array();

                                $label_sufex = $this->label_R_cerasis($label_sufex);
                                $simple_quotes[$count]['label_sufex'] = $label_sufex;

                                in_array('R', $label_sufex) ? $simple_quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = true : '';
                                ($this->quote_settings['liftgate_resid_delivery'] == "yes") && (in_array("R", $label_sufex)) && in_array('L', $label_sufex) ? $simple_quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true : '';


                                (isset($simple_quotes[$count]['label_sufex']) &&
                                    (!empty($simple_quotes[$count]['label_sufex']))) ?
                                    array_push($simple_quotes[$count]['label_sufex'], "L") : // IF
                                    $simple_quotes[$count]['label_sufex'] = array("L");       // ELSE

                                $simple_quotes[$count]['id'] .= '_WL';
                                // FDO
                                $simple_quotes[$count]['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true;

                                $price_sorted_key[$count] = (isset($simple_quotes[$count]['cost'])) ? $simple_quotes[$count]['cost'] : 0;

                                $count++;
                            }
                        }
                    }
                }
            } else {
                return [];
            }

            // array_multisort 
            (!empty($allServices)) ? array_multisort($parent_price_sorted_key, SORT_ASC, $allServices) : "";
            (!empty($simple_quotes)) ? array_multisort($price_sorted_key, SORT_ASC, $simple_quotes) : "";

            (!empty($simple_quotes)) ? $allServices['simple_quotes'] = $simple_quotes : "";

            if ($cerasis_global_tranz_shipping_service == 'wc_final_mile_service' && $cerasis_global_tranz_api_endpoint == 'wc_cerasis_api_fields') {
                $simple_quotes = (!empty($simple_quotes) && (is_array($simple_quotes))) ? array_slice($simple_quotes, 0, 1) : $simple_quotes;
                $allServices = (!empty($allServices) && (is_array($allServices))) ? array_slice($allServices, 0, 1) : $allServices;
            }

            return $allServices;
        }

        function destinationAddressCerasis()
        {
            $en_order_accessories = apply_filters('en_order_accessories', []);
            if (isset($en_order_accessories) && !empty($en_order_accessories)) {
                return $en_order_accessories;
            }

            $cerasis_woo_obj = new Engtz_Cerasis_Woo_Update_Changes();
            $freight_zipcode = (strlen(WC()->customer->get_shipping_postcode()) > 0) ? WC()->customer->get_shipping_postcode() : $cerasis_woo_obj->cerasis_postcode();
            $freight_state = (strlen(WC()->customer->get_shipping_state()) > 0) ? WC()->customer->get_shipping_state() : $cerasis_woo_obj->cerasis_getState();
            $freight_country = (strlen(WC()->customer->get_shipping_country()) > 0) ? WC()->customer->get_shipping_country() : $cerasis_woo_obj->cerasis_getCountry();
            $freight_city = (strlen(WC()->customer->get_shipping_city()) > 0) ? WC()->customer->get_shipping_city() : $cerasis_woo_obj->cerasis_getCity();
            $address = (strlen(WC()->customer->get_shipping_address_1()) > 0) ? WC()->customer->get_shipping_address_1() : $cerasis_woo_obj->cerasis_getAddress1();
            return array(
                'city' => $freight_city,
                'state' => $freight_state,
                'zip' => $freight_zipcode,
                'country' => $freight_country,
                'address' => $address,
            );
        }

        /**
         * getting warehouse address
         * @param $warehous_list
         * @param $receiver_zip_code
         * @return array
         */
        public function get_warehouse($warehous_list, $receiver_zip_code)
        {
            if (count($warehous_list) == 1) {
                $warehous_list = reset($warehous_list);
                return $this->cerasis_origin_array($warehous_list);
            }

            $cerasis_distance_request = new Engtz_Get_cerasis_freight_distance();
            $accessLevel = "MultiDistance";
            $response_json = $cerasis_distance_request->cerasis_freight_address($warehous_list, $accessLevel, $this->destinationAddressCerasis());
            $response_json = json_decode($response_json);
            $origin_with_min_dist = isset($response_json->origin_with_min_dist) ? $response_json->origin_with_min_dist : (object)[];
            return $this->cerasis_origin_array($origin_with_min_dist);
        }

        /**
         * getting plugin origin
         * @param $origin
         * @return array
         */
        function cerasis_origin_array($origin)
        {

//      In-store pickup and local delivery
            if (has_filter("engtz_wd_origin_array_set")) {
                return apply_filters("engtz_wd_origin_array_set", $origin);
            }

            $origin_array = array(
                'locationId' => $origin->id,
                'senderZip' => $origin->zip,
                'senderCity' => $origin->city,
                'senderState' => $origin->state,
                'location' => $origin->location,
                'origin_markup' => $origin->origin_markup,
                'senderCountryCode' => $origin->country
            );
            return $origin_array;
        }

        /**
         * All Enabled Carriers List
         * @return array
         * @global $wpdb
         */
        function get_active_carriers()
        {
            global $wpdb;
            $all_carriers = $wpdb->get_results(
                "SELECT * FROM " . $wpdb->prefix . "en_cerasis_account_carriers WHERE `carrier_status`='1'"
            );
            if ($all_carriers) {
                // When liftgate fees added to carriers tab
                $carrier_tab_liftgate = false;
                foreach ($all_carriers as $key => $value) {
                    $liftgate_fee = isset($value->liftgate_fee) ? $value->liftgate_fee : 0;
                    $liftgate_fee > 0 ? $carrier_tab_liftgate = true : '';
                    $carriers[$value->carrier_scac] = $liftgate_fee;
                }

                $carriers['carrier_tab_liftgate'] = $carrier_tab_liftgate;
                return $carriers;
            } else {
                return $carriers = array('Error' => 'Not active carriers found!');
            }
        }

        function addOriginAndProductMarkup($quote, $product_level_markup, $origin_level_markup)
        {
            $cost = isset($quote['ShipmentRate']) ? $quote['ShipmentRate'] : 0;

            // Product level markup
            if (!empty($product_level_markup)) {
                $cost = EnFreightviewResponse::en_add_handling_fee($cost, $product_level_markup);
            }

            // origin level markup
            if (!empty($origin_level_markup)) {
                $cost = EnFreightviewResponse::en_add_handling_fee($cost, $origin_level_markup);
            }            

            return $cost;
        }
        
        /**
         * New API Credentials
         * @param $packages
         * @return array/string
        */
        function new_api_credentials($packages, $doNesting, $modify_shipment_date_time, $order_cut_off_time, $shipment_off_set_days, $store_date_time, $shipment_week_days, $hazardous_material)
        {
            $api_creds = array(
                'clientId' => get_option('wc_gtz_new_api_client_id'),
                'clientSecret' => get_option('wc_gtz_new_api_client_secret'),
                'ApiVersion' => '2.0',
                'speed_freight_username' => get_option('wc_gtz_new_api_api_username'),
                'speed_freight_password' => get_option('wc_gtz_new_api_api_password'),

                'speed_freight_residential_delivery' => get_option('wc_settings_cerasis_residential_delivery') == 'yes' ? 'Y' : 'N',
                'speed_freight_lift_gate_delivery' => (get_option('wc_settings_cerasis_lift_gate_delivery') == 'yes' || get_option('cerasis_freights_liftgate_delivery_as_option') == 'yes') ? 'Y' : 'N',
                'speed_freight_notify_before_delivery' => (get_option('wc_settings_cerasis_notify') == 'yes' || get_option('cerasis_freights_notify_as_option') == 'yes') ? 'Y' : 'N',
                'speed_freight_residential_pickup' => '',
                'insureShipment' => '0',
                'insuranceCategory' => array (),
                'handlingUnitWeight' => get_option('engtz_freight_handling_weight'),
                'maxWeightPerHandlingUnit' => get_option('engtz_freight_maximum_handling_weight'),
                'isGlobalTrazNewApi' => true,
                'modifyShipmentDateTime' => $modify_shipment_date_time,
                'OrderCutoffTime' => $order_cut_off_time,
                'shipmentOffsetDays' => $shipment_off_set_days,
                'storeDateTime' => $store_date_time,
                'shipmentWeekDays' => $shipment_week_days,
                'isUnishipperNewApi' => 'yes',
                'requestFromGlobalTranz' => '1'
           );

           if ($hazardous_material) {
                $api_creds['lineItemHazmatInfo'][] = array(
                    'isHazmatLineItem' => 'Y',
                    'lineItemHazmatUNNumberHeader' => 'UN #',
                    'lineItemHazmatUNNumber' => 'UN 1139', 
                    'lineItemHazmatClass' => '1.1',
                    'lineItemHazmatEmContactPhone' => '4043308699', 
                    'lineItemHazmatPackagingGroup' => 'I', 
                );
           }

            return $api_creds;
        }

        function applyErrorManagement($quotes_request)
        {
            // error management will be applied only for more than 1 product
            if (empty($quotes_request) || empty($quotes_request['commdityDetails']) || (!empty($quotes_request['commdityDetails']) && count($quotes_request['commdityDetails']) < 2)) return $quotes_request;

            $error_option = get_option('error_management_settings_gtz_ltl');

            foreach ($quotes_request['commdityDetails'] as $key => $product) {
                $empty_dims_check = empty($product['lineItemWidth']) || empty($product['lineItemHeight']) || empty($product['lineItemLength']);
                $empty_shipping_class_check = empty($product['lineItemClass']);
                $weight = $product['lineItemWeight'];

                if (empty($weight) || ($empty_dims_check && $empty_shipping_class_check)) {
                    if ($error_option == 'dont_quote_shipping') {
                        $quotes_request['commdityDetails'] = [];
                        break;
                    } else unset($quotes_request['commdityDetails'][$key]);
                }
            }

            $carrier_apis = ['globalTranz', 'wweLTL', 'cerasis'];
            foreach ($carrier_apis as $carrier_api) {
                if (isset($quotes_request['carriers'][$carrier_api], $quotes_request['carriers'][$carrier_api]['api'])) {
                    $quotes_request['carriers'][$carrier_api]['api']['error_management'] = $error_option;
                }
            }
            
            return $quotes_request;
        }
    }

}
