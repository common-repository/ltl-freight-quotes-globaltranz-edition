<?php

/**
 * Customize the api response.
 */

namespace EnFreightviewResponse;

use EnFreightviewOtherRates\EnFreightviewOtherRates;
use EnGtzLtlShippingRulesAjaxReq;

/**
 * Compile the rates.
 * Class EnFreightviewResponse
 * @package EnFreightviewResponse
 */
if (!class_exists('EnFreightviewResponse')) {

    class EnFreightviewResponse
    {
        static public $en_step_for_rates = [];
        static public $en_small_package_quotes = [];
        static public $en_step_for_sender_origin = [];
        static public $en_step_for_product_name = [];
        static public $en_quotes_info_api = [];
        static public $en_accessorial = [];
        static public $en_always_accessorial = [];
        static public $en_settings = [];
        static public $en_package = [];
        static public $en_origin_address = [];
        static public $en_is_shipment = '';
        static public $en_auto_residential_status = '';
        static public $en_hazardous_status = '';
        static public $rates;
        static public $standard_packaging = [];
        // FDO
        static public $en_fdo_meta_data = [];
        static public $new_api_creds = [];
        static public $old_api_creds = [];
        static public $quotes_without_lfg = [];
        static public $en_accessorial_excluded;

        /**
         * Address set for order widget
         * @param array $sender_origin
         * @return string
         */
        static public function en_step_for_sender_origin($sender_origin)
        {
            return $sender_origin['location'] . ": " . $sender_origin['city'] . ", " . $sender_origin['state'] . " " . $sender_origin['zip'];
        }

        /**
         * filter detail for order widget detail
         * @param array $en_package
         * @param mixed $key
         */
        static public function en_save_detail_for_order_widget($en_package, $key)
        {
            // FDO
            self::$en_fdo_meta_data = (isset($en_package['meta_data'][$key]['en_fdo_meta_data'])) ? $en_package['meta_data'][$key]['en_fdo_meta_data'] : [];
            self::$en_step_for_sender_origin = (isset($en_package['meta_data'][$key]['origin'])) ?  self::en_step_for_sender_origin($en_package['meta_data'][$key]['origin']) : [];
            self::$en_step_for_product_name = (isset($en_package['meta_data'][$key]['product_name'])) ? $en_package['meta_data'][$key]['product_name'] : [];
            self::$en_hazardous_status = (isset($en_package['meta_data'][$key]['hazardous']) && in_array('H', $en_package['meta_data'][$key]['hazardous'])) ? 'h' : '';
        }

        /**
         * Shipping rates
         * @param array $response
         * @param array $en_package
         * @return array
         */
        static public function en_rates($response, $en_package, $en_small_package_quotes, $quote_settings)
        {
            self::$en_settings = $quote_settings;
            self::$rates = $instor_pickup_local_delivery = [];
            self::$en_package = $en_package;
            self::$en_small_package_quotes = $en_small_package_quotes;
            $en_response = (!empty($response) && is_array($response)) ? $response : [];
            $en_response = self::en_is_shipment_api_response($en_response);

            foreach ($en_response as $key => $value) {
                if (empty($value) || !is_array($value)) {
                    return [];
                }
                
                self::$quotes_without_lfg = [];
                self::en_save_detail_for_order_widget(self::$en_package, $key);
                self::$en_step_for_rates = $value;

                $autoResidentialSubscriptionExpired = $hazardousStatus = $autoResidentialStatus = $autoResidentialsStatus = '';
                extract($value);

                $residential_detecion_flag = get_option("en_woo_addons_auto_residential_detecion_flag");
                $auto_renew_plan = get_option("auto_residential_delivery_plan_auto_renew");

                if (($auto_renew_plan == "disable") &&
                    ($residential_detecion_flag == "yes") && $autoResidentialSubscriptionExpired == 1) {
                    update_option("en_woo_addons_auto_residential_detecion_flag", "no");
                }

                (isset(self::$en_package['meta_data'][$key]['origin'])) ? self::$en_origin_address = self::$en_package['meta_data'][$key]['origin'] : '';

                self::$en_auto_residential_status = $autoResidentialsStatus;

                $instor_pickup_local_delivery = self::en_sanitize_rate('InstorPickupLocalDelivery', []);

                $debug = self::en_sanitize_rate('debug', []);

                // Pallet packaging
                self::$standard_packaging = self::en_sanitize_rate('standardPackagingData', []);

                $severity = self::en_sanitize_rate('severity', '');
                if (is_string($severity) && strlen($severity) > 0 && strtolower($severity) == 'error') {
                    return [];
                }

                // Excluded accessoarials
                if (!empty(self::en_sanitize_rate('liftgateExcluded', '')) && self::en_sanitize_rate('liftgateExcluded', '') == '1') {
                    isset(self::$en_settings['liftgate_delivery']) && self::$en_settings['liftgate_delivery'] = 'no';
                    isset(self::$en_settings['liftgate_delivery_option']) && self::$en_settings['liftgate_delivery_option'] = 'no';
                    isset(self::$en_settings['liftgate_resid_delivery']) && self::$en_settings['liftgate_resid_delivery'] = "no";
                    self::$en_accessorial_excluded = ['liftgateResidentialExcluded'];
                    add_filter('en_gtz_ltl_accessorial_excluded', [__CLASS__, 'en_gtz_ltl_accessorial_excluded'], 10, 1);
                    isset(self::$en_fdo_meta_data['accessorials']['liftgate']) && self::$en_fdo_meta_data['accessorials']['liftgate'] = false;
                    isset(self::$en_fdo_meta_data['accessorials']['residential']) && self::$en_fdo_meta_data['accessorials']['residential'] = false;
                }

                // Apply override rates shipping rules
                $shipping_rules_obj = new EnGtzLtlShippingRulesAjaxReq();
                self::$en_step_for_rates = $shipping_rules_obj->apply_shipping_rules($en_package, true, $value, $key);
                
                self::$new_api_creds = !empty(self::en_sanitize_rate('newAPICredentials', [])) ? $value : self::$new_api_creds;
                self::$old_api_creds = !empty(self::en_sanitize_rate('oldAPICredentials', [])) ? $value : self::$old_api_creds;

                $origin_level_markup = isset($en_package['origin_markup'][$key]) ? $en_package['origin_markup'][$key] : 0;
                $product_level_markup = isset($en_package['product_level_markup'][$key]) ? $en_package['product_level_markup'][$key] : 0;

                self::$en_fdo_meta_data['accessorials']['residential'] = !empty($residentialStatus) && $residentialStatus == 'r';
                self::$quotes_without_lfg = self::en_sanitize_rate('quotesWithoutAccessorial', []);
                self::en_arrange_rates(self::en_sanitize_rate('q', []), $debug, $product_level_markup, $origin_level_markup);
            }

            self::updateAPISelection();
            self::$rates = EnFreightviewOtherRates::en_extra_custom_services
            (
                $instor_pickup_local_delivery, self::$en_is_shipment, self::$en_origin_address, self::$rates, self::$en_settings
            );

            return self::$rates;
        }

        /**
         * Multi shipment query
         * @param array $en_rates
         * @param string $accessorial
         */
        static public function en_multi_shipment($en_rates, $accessorial)
        {
            $engtz_version_compat = new \Engtz_VersionCompat();
            $en_rates = (isset($en_rates) && (is_array($en_rates))) ? array_slice($en_rates, 0, 1) : [];
            $en_calculated_cost = array_sum($engtz_version_compat->engtz_array_column($en_rates, 'cost'));

            $en_fdo_meta_data = [];
            $en_1st_rate_id = $en_1st_rate_label = '';
            if (is_array($en_rates) && !empty($en_rates)) {
                $en_1st_rate = (isset($en_rates['0'])) ? $en_rates['0'] : '';
                $en_1st_rate_id = (isset($en_1st_rate['id'])) ? $en_1st_rate['id'] : '';
                $en_1st_rate_label = (isset($en_1st_rate['label'])) ? $en_1st_rate['label'] : '';
                // FDO
                $en_fdo_meta_data[] = (isset($en_1st_rate['meta_data']['en_fdo_meta_data'])) ? $en_1st_rate['meta_data']['en_fdo_meta_data'] : [];
            }

            if (isset(self::$rates[$accessorial])) {
                self::$rates[$accessorial]['id'] = $en_1st_rate_id;
                self::$rates[$accessorial]['cost'] += $en_calculated_cost;
                self::$rates[$accessorial]['min_prices'] = array_merge(self::$rates[$accessorial]['min_prices'], $en_rates);
                self::$rates[$accessorial]['plugin_name'] = 'globalTranz';
                self::$rates[$accessorial]['plugin_type'] = 'ltl';
                self::$rates[$accessorial]['owned_by'] = 'eniture';
                // FDO
                self::$rates[$accessorial]['en_fdo_meta_data'] = array_merge(self::$rates[$accessorial]['en_fdo_meta_data'], $en_fdo_meta_data);
            } else {
                self::$rates[$accessorial] = [
                    'id' => $accessorial,
                    'label' => $en_1st_rate_label,
                    'label' => 'Freight',
                    'cost' => $en_calculated_cost,
                    'label_sufex' => str_split($accessorial),
                    'min_prices' => $en_rates,
                    // FDO
                    'en_fdo_meta_data' => $en_fdo_meta_data,
                    'plugin_name' => 'globalTranz',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture',
                ];
            }
        }

        /**
         * Single shipment query
         * @param array $en_rates
         * @param string $accessorial
         */
        static public function en_single_shipment($en_rates, $accessorial)
        {
            self::$rates = array_merge(self::$rates, $en_rates);
        }

        /**
         * Sanitize the value from array
         * @param string $index
         * @param dynamic $is_not_matched
         * @return dynamic mixed
         */
        static public function en_sanitize_rate($index, $is_not_matched)
        {
            return (isset(self::$en_step_for_rates[$index])) ? self::$en_step_for_rates[$index] : $is_not_matched;
        }

        /**
         * There is single or multiple shipment
         * @param array $en_response
         */
        static public function en_is_shipment_api_response($en_response)
        {
            if (isset($en_response['quotesInfo'])) {
                self::$en_quotes_info_api = $en_response['quotesInfo'];
                unset($en_response['quotesInfo']);
            }
            self::$en_is_shipment = count($en_response) > 1 || count(self::$en_small_package_quotes) > 0 ? 'en_multi_shipment' : 'en_single_shipment';
            return $en_response;
        }

        /**
         * Get accessorials prices from api response
         * @param array $accessorials
         * @return array
         */
        static public function en_get_accessorials_prices($accessorials, $liftgate_resid_delivery)
        {
            $surcharges = [];
            $mapp_surcharges = [
                'Residential Delivery' => 'R',
                'Liftgate Delivery' => 'L',
                'Notify prior to arrival' => 'N',
                'Limited Access Delivery' => 'A'
            ];
            $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
            if (stripos(implode($all_plugins), 'residential-address-detection.php') || is_plugin_active_for_network('residential-address-detection/residential-address-detection.php')) {
                $rad_status = get_option('residential_delivery_options_disclosure_types_to') != 'not_show_r_checkout';
                if($rad_status == false && get_option('suspend_automatic_detection_of_residential_addresses') != 'yes') {
                    unset($mapp_surcharges['Residential Delivery']);
                }
            }
            foreach ($accessorials as $index => $accessorial) {
                $key = (isset($accessorial['Name'])) ? $accessorial['Name'] : '';
                $amount = (isset($accessorial['Charge'])) ? $accessorial['Charge'] : 0;
                if (isset($mapp_surcharges[$key])) {
                    in_array($mapp_surcharges[$key], self::$en_always_accessorial) ?
                        $amount = 0 : '';
                    self::$en_auto_residential_status == 'r' && $mapp_surcharges[$key] == 'R' ? $amount = 0 : '';
                    self::$en_auto_residential_status == 'r' && $liftgate_resid_delivery == 'yes' && $mapp_surcharges[$key] == 'L' ? $amount = 0 : '';
                    $surcharges[$mapp_surcharges[$key]] = $amount;
                }
            }

            return $surcharges;
        }

        /**
         * Get quote settings detail
         * @param array $en_settings
         * @return array
         */
        static public function en_freightview_compare_accessorial($en_settings)
        {
            $en_gtz_accessorial[] = ['S'];
            $en_settings['liftgate_delivery_option'] == 'yes' ? $en_gtz_accessorial[] = ['L'] : "";
            $en_settings['notify_delivery_option'] == 'yes' ? $en_gtz_accessorial[] = ['N'] : "";
            $en_settings['liftgate_delivery_option'] == 'yes' && $en_settings['notify_delivery_option'] == 'yes' ? $en_gtz_accessorial[] = ['N', 'L'] : "";
            $en_settings['limited_access_delivery_option'] == 'yes' ? $en_gtz_accessorial[] = ['A'] : "";
            $en_settings['liftgate_delivery_option'] == 'yes' && $en_settings['limited_access_delivery_option'] == 'yes' ? $en_gtz_accessorial[] = ['A', 'L'] : "";
            $en_settings['notify_delivery_option'] == 'yes' && $en_settings['limited_access_delivery_option'] == 'yes' ? $en_gtz_accessorial[] = ['A', 'N'] : "";
            $en_settings['liftgate_delivery_option'] == 'yes' && $en_settings['notify_delivery_option'] == 'yes' && $en_settings['limited_access_delivery_option'] == 'yes' ? $en_gtz_accessorial[] = ['L', 'N', 'A'] : "";

            return $en_gtz_accessorial;
        }

        /**
         * Set quote settings detail
         */
        static public function en_freightview_always_accessorials($en_settings)
        {
            $accessorials = [];
            $en_settings['liftgate_delivery'] == 'yes' ? $accessorials[] = 'L' : "";
            $en_settings['residential_delivery'] == 'yes' ? $accessorials[] = 'R' : "";
            $en_settings['notify_delivery'] == 'yes' ? $accessorials[] = 'N' : "";
            $en_settings['limited_access_delivery'] == 'yes' ? $accessorials[] = 'A' : "";

            return $accessorials;
        }

        /**
         * All Enabled Carriers List
         * @return array
         * @global $wpdb
         */
        static public function get_gt_carriers()
        {
            global $wpdb;
            $all_carriers = $wpdb->get_results(
                "SELECT * FROM " . $wpdb->prefix . "gt_carriers WHERE `carrier_status`='1'"
            );
            $carrier_list = [];
            if ($all_carriers) {
                foreach ($all_carriers as $carrier) {
                    $gtz_scac = (isset($carrier->gtz_scac)) ? $carrier->gtz_scac : '';
                    $gtz_name = (isset($carrier->gtz_name)) ? $carrier->gtz_name : '';
                    $carrier_list[$gtz_scac] = $gtz_name;
                }
                return $carrier_list;
            } else {
                return $carrier_list = array('Error' => 'Not active carriers found!');
            }
        }

        /**
         * Filter quotes
         * @param array $rates
         */
        static public function en_arrange_rates($rates, $debug, $product_level_markup, $origin_level_markup)
        {
            $en_rates = [];
            $en_sorting_rates = [];
            $en_count_rates = 0;

            $lowest_label = get_option('wc_settings_globaltranz_lowest_label_as');
            $quickest_label = get_option('wc_settings_globaltranz_quickest_label_as');
            $rating_checkbox = get_option('service_global_tranz_rating_method_ch_unch');
            $active_carrier = self::get_gt_carriers();

            $global_tranz_service = [
                'LowestCostRate' => [
                    'service' => get_option('service_global_tranz_lowest_cost_rate_quotes'),
                    'label' => isset($lowest_label) && strlen($lowest_label) > 0 ? $lowest_label : 'Lowest Cost Rate'
                ],
                'QuickestTransitRate' => [
                    'service' => get_option('service_global_tranz_quickest_transit_rate_quotes'),
                    'label' => isset($quickest_label) && strlen($quickest_label) > 0 ? $quickest_label : 'Quickest Transit Rate',
                ],
            ];

            $handling_fee = $en_settings_label = $rating_method = $delivery_estimates = $enable_carriers = $liftgate_resid_delivery = $liftgate_delivery_option = '';
            self::$en_accessorial = self::en_freightview_compare_accessorial(self::$en_settings);
            self::$en_always_accessorial = self::en_freightview_always_accessorials(self::$en_settings);
            extract(self::$en_settings);

            // Eniture Debug Mood
            do_action("eniture_debug_mood", "GlobalTranz Settings ", self::$en_settings);
            do_action("eniture_debug_mood", "GlobalTranz Accessorials ", self::$en_accessorial);
            $is_valid_label = false;
            // is quote settings label will be show or not
            switch (self::$en_is_shipment) {

                case 'en_single_shipment':
                    switch ($rating_method) {
                        case 'Cheapest' && strlen($en_settings_label) > 0:
                            $is_valid_label = true;
                            break;
                        case 'average_rate':
                            $en_settings_label = strlen($en_settings_label) > 0 ? $en_settings_label : 'Freight';
                            $is_valid_label = true;
                            break;
                    }
                    break;
                default:
                    $is_valid_label = false;
                    break;
            }

            $new_api_enabled = get_option('cerasis_global_tranz_api_endpoint') == 'wc_global_tranz_new_api_fields';

            foreach ($rates as $ltl_service_type_name => $en_rate) {
                if ($new_api_enabled) {
                    $en_rate = self::formatQuoteDetails($en_rate);
                    $skipService = isset($en_rate['serviceLevel']) && strtolower($en_rate['serviceLevel']) != 'standard';
                    
                    if ($skipService) {
                        continue;
                    }
                }

                $en_rate = self::addLimitedAceesFeeInRate($en_rate);
                self::$en_step_for_rates = $en_rate;

                $en_total_net_charge = self::en_sanitize_rate('LtlAmount', 0);

                // Product level markup
                if (!empty($product_level_markup)) {
                    $en_total_net_charge = self::en_add_handling_fee($en_total_net_charge, $product_level_markup);
                }

                // origin level markup
                if (!empty($origin_level_markup)) {
                    $en_total_net_charge = self::en_add_handling_fee($en_total_net_charge, $origin_level_markup);
                }

                if ((isset($global_tranz_service[$ltl_service_type_name]) && $global_tranz_service[$ltl_service_type_name]['service'] == 'yes') || $rating_checkbox == 'yes') {
                    if ($rating_checkbox == 'yes') {
                        $carrier_code = (isset($en_rate['CarrierDetail']['CarrierCode'])) ? $en_rate['CarrierDetail']['CarrierCode'] : '';
                        if (!isset($active_carrier[$carrier_code])) {
                            continue;
                        }
                        $label = ($is_valid_label && strlen($en_settings_label) > 0) ? $en_settings_label : $active_carrier[$carrier_code];
                    } else {
                        $label = (isset($global_tranz_service[$ltl_service_type_name]['label'])) ? $global_tranz_service[$ltl_service_type_name]['label'] : 'Freight';
                    }
                    // cuttoff time
                    $cutoff_time = '';

                    if (self::$en_is_shipment == 'en_single_shipment' && $delivery_estimates == 'delivery_date') {
                        $calculated_transit_days = self::en_sanitize_rate('LtlDeliveryDate', '');
                        $cutoff_time = ' (Expected delivery by ' . date('m-d-Y', strtotime($calculated_transit_days)) . ')';
                    } else if (self::$en_is_shipment == 'en_single_shipment' && $delivery_estimates == 'delivery_days') {
                        $calculated_transit_days = self::en_sanitize_rate('totalTransitTimeInDays', '');
                        $cutoff_time = ' (Intransit days: ' . $calculated_transit_days . ')';
                    }

                    // make data for order widget detail
                    $meta_data['service_type'] = $label;
                    $meta_data['accessorials'] = wp_json_encode(self::$en_always_accessorial);
                    $meta_data['sender_origin'] = self::$en_step_for_sender_origin;
                    $meta_data['product_name'] = wp_json_encode(self::$en_step_for_product_name);
                    $meta_data['standard_packaging'] = wp_json_encode(self::$standard_packaging);

                    // FDO
                    self::$en_fdo_meta_data['handling_unit_details'] = $debug;
                    $meta_data['en_fdo_meta_data'] = self::$en_fdo_meta_data;

                    // standard rate
                    $rate = [
                        'id' => $label,
                        'label' => $label,
                        'cost' => $en_total_net_charge,
                        'surcharges' => self::en_get_accessorials_prices(self::en_sanitize_rate('Charges', ''), $liftgate_resid_delivery),
                        'meta_data' => $meta_data,
                        'cutoff_time' => $cutoff_time,
                        'plugin_name' => 'globalTranz',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture',
                        'surchargesCost' => isset(self::$en_step_for_rates['surchargesCost']) ? self::$en_step_for_rates['surchargesCost'] : [],
                        'carrier_code' => isset($en_rate['CarrierDetail']['CarrierCode']) ? $en_rate['CarrierDetail']['CarrierCode'] : ''
                    ];

                    foreach (self::$en_accessorial as $key => $accessorial) {
                        $en_fliped_accessorial = array_flip($accessorial);

                        // When auto-rad detected
                        if (self::$en_auto_residential_status == 'r' && !in_array('A', $accessorial)) {
                            $accessorial[] = 'R';

                            if ($liftgate_resid_delivery == 'yes') {
                                if ($liftgate_delivery_option == 'yes' && !in_array('L', $accessorial)) {
                                    continue;
                                } else {
                                    !in_array('L', $accessorial) ? $accessorial[] = 'L' : '';
                                }
                            }
                        }

                        // Add residential fee in surchages array in case of limited access delivery
                        if (in_array('A', $accessorial)) {
                            $rate['surcharges']['R'] = isset($rate['surchargesCost']['Residential Delivery']) ? $rate['surchargesCost']['Residential Delivery'] : 0;
                        }

                        self::$en_step_for_rates = $rate;

                        $en_accessorial_charges = array_diff_key(self::en_sanitize_rate('surcharges', []), $en_fliped_accessorial);

                        $en_accessorial_type = implode('', $accessorial);
                        self::$en_step_for_rates = $en_rates[$en_accessorial_type][$en_count_rates] = $rate;

                        // Cost of the rates
                        $en_sorting_rates
                        [$en_accessorial_type]
                        [$en_count_rates]['cost'] = // Used for sorting of rates
                        $en_rates
                        [$en_accessorial_type]
                        [$en_count_rates]['cost'] = self::en_sanitize_rate('cost', 0) - array_sum($en_accessorial_charges);

                        $en_rates
                        [$en_accessorial_type]
                        [$en_count_rates]['cost'] = self::en_add_handling_fee
                        (
                            $en_rates
                            [$en_accessorial_type]
                            [$en_count_rates]['cost'], $handling_fee
                        );

                        // When hazardous materials detected
                        self::$en_hazardous_status == 'h' ? $accessorial[] = 'H' : '';

                        if (in_array('R', $accessorial)) {
                            $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = true;
                        }
                        if (in_array('L', $accessorial)) {
                            $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true;
                        }
                        if (in_array('N', $accessorial)) {
                            $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['en_fdo_meta_data']['accessorials']['notify'] = true;
                        }
                        if (in_array('A', $accessorial)) {
                            $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['en_fdo_meta_data']['accessorials']['limitedaccess'] = true;
                            $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = false;
                        }
                        $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['label_sufex'] = wp_json_encode($accessorial);
                        $en_rates[$en_accessorial_type][$en_count_rates]['label_sufex'] = $accessorial;
                        $en_rates[$en_accessorial_type][$en_count_rates]['id'] .= $en_accessorial_type;
                        $calculated_rate = $en_rates[$en_accessorial_type][$en_count_rates];

                        // FDO
                        $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['en_fdo_meta_data']['rate'] = [
                            'id' => $calculated_rate['id'],
                            'label' => $calculated_rate['label'],
                            'cost' => $calculated_rate['cost'],
                            'plugin_name' => 'globalTranz',
                            'plugin_type' => 'ltl',
                            'owned_by' => 'eniture',
                        ];
                    }

                    $en_count_rates++;
                }
            }

            $filter_quotes_obj = new \Engtz_Cerasis_Quotes();
            // Override simple quotes cost with WS standard quotes cost in case of separate liftgate quotes of GTZ API
            $en_rates = self::overrideSimpleRatesCost($en_rates, $product_level_markup, $origin_level_markup);

            foreach ($en_rates as $accessorial => $services) {
                (!empty($en_rates[$accessorial])) ? array_multisort($en_sorting_rates[$accessorial], SORT_ASC, $en_rates[$accessorial]) : $en_rates[$accessorial] = [];
                $en_is_shipment = self::$en_is_shipment;
                if ($rating_checkbox == 'yes') {
                    self::$en_is_shipment($filter_quotes_obj->calculate_quotes($en_rates[$accessorial], self::$en_settings), $accessorial);
                } else {
                    self::$en_is_shipment($en_rates[$accessorial], $accessorial);
                }
            }
        }

        /**
         * Generic function to add handling fee in cost of the rate
         * @param float $price
         * @param float $en_handling_fee
         * @return float
         */
        static public function en_add_handling_fee($price, $en_handling_fee)
        {
            $handling_fee = 0;
            if ($en_handling_fee != '' && $en_handling_fee != 0) {
                if (strrchr($en_handling_fee, "%")) {

                    $percent = (float)$en_handling_fee;
                    $handling_fee = (float)$price / 100 * $percent;
                } else {
                    $handling_fee = (float)$en_handling_fee;
                }
            }

            $handling_fee = self::en_smooth_round($handling_fee);
            $price = (float)$price + $handling_fee;
            return $price;
        }

        /**
         * Round the cost of the quote
         * @param float type $val
         * @param int type $min
         * @param int type $max
         * @return float type
         */
        static public function en_smooth_round($val, $min = 2)
        {
            return number_format($val, $min, ".", "");
        }

        static public function formatQuoteDetails($quote)
        {
            $quote['LtlAmount'] = !empty($quote['totalNetCharge']['Amount']) ? $quote['totalNetCharge']['Amount'] : 0;
            $quote['CarrierDetail'] = ['CarrierCode' => !empty($quote['serviceType']) ? $quote['serviceType'] : ''];
            $quote['LtlDeliveryDate'] = !empty($quote['deliveryTimestamp']) ? $quote['deliveryTimestamp'] : '';

            $surcharges = [];
            if (!empty($quote['surcharges'])) {
                if (isset($quote['surcharges']['liftgateFee'])) {
                    $surcharges[] = ['Name' => 'Liftgate Delivery', 'Charge' => $quote['surcharges']['liftgateFee']];
                }

                if (isset($quote['surcharges']['residentialFee'])) {
                    $surcharges[] = ['Name' => 'Residential Delivery', 'Charge' => $quote['surcharges']['residentialFee']];
                }

                if (isset($quote['surcharges']['notifyDeliveryFee'])) {
                    $surcharges[] = ['Name' => 'Notify prior to arrival', 'Charge' => $quote['surcharges']['notifyDeliveryFee']];
                }
            }

            $quote['Charges'] = $surcharges;

            return $quote;
        }

        static public function updateAPISelection()
        {
            // New API to Old API migration
            $newAPICredentials = isset(self::$new_api_creds['newAPICredentials']) ? self::$new_api_creds['newAPICredentials'] : [];
            
            if (!empty($newAPICredentials) && isset($newAPICredentials['client_id']) && isset($newAPICredentials['client_secret'])) {
                $username = get_option('wc_settings_global_tranz_username');
                $password = get_option('wc_settings_global_tranz_password');

                // Update customer's API selection and creds info
                update_option('cerasis_global_tranz_api_endpoint', 'wc_global_tranz_new_api_fields');
                update_option('wc_gtz_new_api_client_id', $newAPICredentials['client_id']);
                update_option('wc_gtz_new_api_client_secret', $newAPICredentials['client_secret']);
                update_option('wc_gtz_new_api_api_username', $username);
                update_option('wc_gtz_new_api_api_password', $password);
            }

            // Old API to New API migration
            $oldAPICredentials = isset(self::$old_api_creds['oldAPICredentials']) ? self::$old_api_creds['oldAPICredentials'] : [];
            if (!empty($oldAPICredentials) && isset($oldAPICredentials['account_number'])) {
                // Update customer's API selection
                update_option('cerasis_global_tranz_api_endpoint', 'wc_global_tranz_api_fields');
            }
        }

        static public function addLimitedAceesFeeInRate($rate)
        {
            if (empty($rate)) {
                return $rate;
            }

            extract(self::$en_settings);
            $limited_access_delivery_fee = !empty($limited_access_delivery_fee) ? $limited_access_delivery_fee : 0;

            $is_residential_address = false;
            if (!empty($rate['Charges'])) {
                foreach ($rate['Charges'] as $value) {
                    if (isset($value['Name']) && $value['Name']  == 'Residential Delivery' && isset($value['Charge']) && $value['Charge'] > 0 ) {
                        $is_residential_address = true;
                        break;
                    }
                }
            }

            if ($limited_access_delivery_option == 'yes' || ($limited_access_delivery == 'yes' && $is_residential_address == false)) {
                $surchargesCost = [];

                if (isset($rate['LtlAmount'])) {
                    $rate['LtlAmount'] += $limited_access_delivery_fee;
                }

                if (isset($rate['Charges'])) {
                    $rate['Charges'][] = [
                        'Name' => 'Limited Access Delivery',
                        'Charge' => $limited_access_delivery_fee,
                        'AccessorialID' => '25'
                    ];

                    foreach ($rate['Charges'] as $surcharge) {
                        $surchargesCost[$surcharge['Name']] = $surcharge['Charge'];
                    }

                    $rate['surchargesCost'] = $surchargesCost;
                }
            } else {
                foreach (self::$en_always_accessorial as $key => $value) {
                    if ($value == 'A') {
                        unset(self::$en_always_accessorial[$key]);
                    }
                }

                if (isset(self::$en_fdo_meta_data['accessorials'])) {
                    unset(self::$en_fdo_meta_data['accessorials']['limitedaccess']);
                }
            }

            return $rate;
        }

        static public function overrideSimpleRatesCost($rates, $product_level_markup, $origin_level_markup)
        {
            $gtz_api_enabled = get_option('cerasis_global_tranz_api_endpoint') == 'wc_global_tranz_api_fields';
            if (empty($rates) || !$gtz_api_enabled) return $rates;

            if (!empty(self::$quotes_without_lfg) && !empty($rates)) {
                $simple_quotes = isset($rates['S']) ? $rates['S'] : [];
                if (empty($simple_quotes)) return $rates;

                $handling_fee = '';
                extract(self::$en_settings);

                foreach ($simple_quotes as $key => $value) {
                    foreach (self::$quotes_without_lfg as $quote) {
                        $code = isset($quote['CarrierDetail']['CarrierCode']) ? $quote['CarrierDetail']['CarrierCode'] : '';
                        $cost = isset($quote['LtlAmount']) ? $quote['LtlAmount'] : '';

                        $cost = !empty($product_level_markup) ? self::en_add_handling_fee($cost, $product_level_markup) : $cost;
                        $cost = !empty($origin_level_markup) ? self::en_add_handling_fee($cost, $origin_level_markup) : $cost;
                        $cost = !empty($handling_fee) ? self::en_add_handling_fee($cost, $handling_fee) : $cost;

                        if (isset($value['carrier_code']) && $value['carrier_code'] == $code) {
                            $rates['S'][$key]['cost'] = $cost;
                        }
                    }
                }
            }

            return $rates;
        }

        /**
         * Accessoarials excluded
         * @param $excluded
         * @return array
        */
        static public function en_gtz_ltl_accessorial_excluded($excluded)
        {
            return array_merge($excluded, self::$en_accessorial_excluded);
        }
    }

}
