<?php

/**
 * Shipping Method Class
 * @package     Woocommerce GlobalTranz Edition
 * @author      <https://eniture.com/>
 * @version     v.1..0 (01/10/2017)
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * shipping method function to initiate the shipping calculation
 */
if (!function_exists('en_gtz_shipping_method_init')) {

    function en_gtz_shipping_method_init()
    {
        if (!class_exists('Engtz_GlobalTranz_Shipping_Method')) {

            /**
             * Shipping Method Class | shipping method function to initiate the shipping calculation
             */
            class Engtz_GlobalTranz_Shipping_Method extends WC_Shipping_Method
            {
                public $forceAllowShipMethodCortigo = array();
                public $getPkgObjCortigo;
                public $cerasis_res_inst;
                public $instore_pickup_and_local_delivery;
                public $web_service_inst;
                public $package_plugin;
                public $InstorPickupLocalDelivery;
                public $woocommerce_package_rates;
                public $shipment_type;
                public $allow_arrangements;
                public $rate_method;
                public $estimate_delivery;
                public $label_as;
                public $option_number;
                public $arrangement_text;
                public $quote_settings;
                // FDO
                public $en_fdo_meta_data = [];
                public $en_fdo_meta_data_third_party = [];
                public $minPrices;

                /**
                 * Shipping method class constructor
                 * @param $instance_id
                 * @global $woocommerce
                 */
                public function __construct($instance_id = 0)
                {
                    $this->allow_arrangements = get_option('wc_settings_cerasis_allow_for_own_arrangment');
                    $this->rate_method = get_option('wc_settings_cerasis_rate_method');
                    $this->estimate_delivery = get_option('wc_settings_cerasis_delivery_estimate');
                    $this->label_as = get_option('wc_settings_cerasis_label_as');
                    $this->option_number = get_option('wc_settings_cerasis_Number_of_options');
                    $this->arrangement_text = get_option('wc_settings_cerasis_text_for_own_arrangment');
                    $this->id = 'engtz_cerasis_shipping_method';
                    $this->instance_id = absint($instance_id);
                    $this->method_title = __('GlobalTranz');
                    $this->method_description = __('Shipping rates from GlobalTranz.');
                    $this->supports = array(
                        'shipping-zones',
                        'instance-settings',
                        'instance-settings-modal',
                    );
                    $this->enabled = "yes";
                    $this->title = "LTL Freight Quotes – GlobalTranz Edition";
                    $this->init();
                }

                /**
                 * shipping method initiate the form fields
                 */
                function init()
                {
                    $this->init_form_fields();
                    $this->init_settings();
                    add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
                }

                /**
                 * shipping method enable/disable checkbox for shipping service
                 */
                public function init_form_fields()
                {
                    $this->instance_form_fields = array(
                        'enabled' => array(
                            'title' => __('Enable / Disable', 'woocommerce'),
                            'type' => 'checkbox',
                            'label' => __('Enable This Shipping Service', 'woocommerce'),
                            'default' => 'yes',
                            'id' => 'cerasis_enable_disable_shipping'
                        )
                    );
                }

                /**
                 * Third party quotes
                 * @param type $forceShowMethods
                 * @return type
                 */
                public function forceAllowShipMethodCortigo($forceShowMethods)
                {
                    if (!empty($this->getPkgObjCortigo->ValidShipmentsArr) && (!in_array("ltl_freight", $this->getPkgObjCortigo->ValidShipmentsArr))) {
                        $this->forceAllowShipMethodCortigo[] = "free_shipping";
                        $this->forceAllowShipMethodCortigo[] = "valid_third_party";
                    } else {

                        $this->forceAllowShipMethodCortigo[] = "ltl_shipment";
                    }

                    $forceShowMethods = array_merge($forceShowMethods, $this->forceAllowShipMethodCortigo);

                    return $forceShowMethods;
                }

                /**
                 * Virtual Products
                 */
                public function en_virtual_products()
                {
                    global $woocommerce;
                    $products = $woocommerce->cart->get_cart();
                    $items = $product_name = [];
                    foreach ($products as $key => $product_obj) {
                        $product = $product_obj['data'];
                        $is_virtual = $product->get_virtual();

                        if ($is_virtual == 'yes') {
                            $attributes = $product->get_attributes();
                            $product_qty = $product_obj['quantity'];
                            $product_title = str_replace(array("'", '"'), '', $product->get_title());
                            $product_name[] = $product_qty . " x " . $product_title;

                            $meta_data = [];
                            if (!empty($attributes)) {
                                foreach ($attributes as $attr_key => $attr_value) {
                                    $meta_data[] = [
                                        'key' => $attr_key,
                                        'value' => $attr_value,
                                    ];
                                }
                            }

                            $items[] = [
                                'id' => $product_obj['product_id'],
                                'name' => $product_title,
                                'quantity' => $product_qty,
                                'price' => $product->get_price(),
                                'weight' => 0,
                                'length' => 0,
                                'width' => 0,
                                'height' => 0,
                                'type' => 'virtual',
                                'product' => 'virtual',
                                'sku' => $product->get_sku(),
                                'attributes' => $attributes,
                                'variant_id' => 0,
                                'meta_data' => $meta_data,
                            ];
                        }
                    }

                    $virtual_rate = [];

                    if (!empty($items)) {
                        $virtual_rate = [
                            'id' => 'en_virtual_rate',
                            'label' => 'Virtual Quote',
                            'cost' => 0,
                        ];

                        $virtual_fdo = [
                            'plugin_type' => 'ltl',
                            'plugin_name' => 'globalTranz',
                            'accessorials' => '',
                            'items' => $items,
                            'address' => '',
                            'handling_unit_details' => '',
                            'rate' => $virtual_rate,
                        ];

                        $meta_data = [
                            'sender_origin' => 'Virtual Product',
                            'product_name' => wp_json_encode($product_name),
                            'en_fdo_meta_data' => $virtual_fdo,
                        ];

                        $virtual_rate['meta_data'] = $meta_data;

                    }

                    return $virtual_rate;
                }

                /**
                 * shipping method rate calculation
                 * @param $package
                 * @return boolean
                 */
                public function calculate_shipping($package = array(), $eniture_admin_order_action = false)
                {
                    if (is_admin() && !wp_doing_ajax() && !$eniture_admin_order_action) {
                        return [];
                    }

                    // Eniture debug mood
                    do_action("eniture_error_messages", "Errors");
                    $this->package_plugin = get_option('cerasis_freight_package');

                    $coupon = WC()->cart->get_coupons();

                    if (isset($coupon) && !empty($coupon)) {
                        $free_shipping = $this->cerasis_shipping_rate_coupon($coupon);
                        if ($free_shipping == 'y')
                            return [];
                    }
                    $this->instore_pickup_and_local_delivery = FALSE;

                    $billing_obj = new Engtz_Billing_Details();
                    $billing_details = $billing_obj->billing_details();
                    $freight_quotes = new Engtz_Quotes_Request();
                    $cart_obj = new Engtz_Cart_To_Request();

                    $this->getPkgObjCortigo = $cart_obj;
                    add_filter('force_show_methods', array($this, 'forceAllowShipMethodCortigo'));
                    $this->cerasis_res_inst = $freight_quotes;

                    $this->web_service_inst = $freight_quotes;

                    $this->ltl_shipping_quote_settings();

                    // -100% Handling Fee is Invalid
                    if (isset($this->cerasis_res_inst->quote_settings['handling_fee']) &&
                        ($this->cerasis_res_inst->quote_settings['handling_fee'] == "-100%")) {
                            $rates = array(
                                'id' => $this->id . ':' . 'free',
                                'label' => 'Free Shipping',
                                'cost' => 0,
                                'plugin_name' => 'globalTranz',
                                'plugin_type' => 'ltl',
                                'owned_by' => 'eniture'
                            );
                            $this->add_rate($rates);
                            
                            return [];
                    }

                    $admin_settings = new Engtz_Admin_Settings();
                    $freight_zipcode = (strlen(WC()->customer->get_shipping_postcode()) > 0) ? $freight_zipcode = WC()->customer->get_shipping_postcode() : $freight_zipcode = $billing_details['postcode'];
                    $freight_package = $cart_obj->cart_to_request($package, $freight_quotes, $freight_zipcode);
                
                    // Apply Hide Methods Shipping Rules
                    $shipping_rule_obj = new EnGtzLtlShippingRulesAjaxReq();
                    $shipping_rules_applied = $shipping_rule_obj->apply_shipping_rules($freight_package);
                    if ($shipping_rules_applied) {
                        return [];
                    }

                    if (isset($freight_package) && empty($freight_package)) {
                        return [];
                    }

                    $handlng_fee = $admin_settings->get_handling_fee();
                    $smallQuotes = [];
                    if (isset($freight_package['shipment_type']) && !empty($freight_package['shipment_type'])) {

                        $smallPluginExist = FALSE;
                        $ltlPluginExist = FALSE;
                        foreach ($freight_package['shipment_type'] as $freight_package_key => $freight_package_value) {
                            if (isset($freight_package_value['cerasis'])) {
                                $ltlPluginExist = TRUE;
                            } elseif (isset($freight_package_value['small'])) {
                                $smallPluginExist = TRUE;
                            }
                        }

                        if ($smallPluginExist && $ltlPluginExist) {
                            $calledMethod = $smallQuotes = [];
                            $eniturePluigns = json_decode(get_option('EN_Plugins'));
                            foreach ($eniturePluigns as $enIndex => $enPlugin) {
                                $freightSmallClassName = 'WC_' . $enPlugin;
                                if (!in_array($freightSmallClassName, $calledMethod)) {
                                    if (class_exists($freightSmallClassName)) {
                                        $smallPluginExist = TRUE;
                                        $SmallClassNameObj = new $freightSmallClassName();
                                        $package['itemType'] = 'ltl';
                                        $smallQuotesResponse = $SmallClassNameObj->calculate_shipping($package, true);
                                        $smallQuotes[] = $smallQuotesResponse;
                                    }
                                    $calledMethod[] = $freightSmallClassName;
                                }
                            }

                            foreach ($freight_package['shipment_type'] as $freight_zip => $freight_shipment) {
                                if (isset($freight_shipment['small'], $freight_package['origin'])) {
                                    foreach ($freight_package['origin'] as $shipment_key => $origin) {
                                        $origin_zip = (isset($origin['zip'])) ? $origin['zip'] : '';
                                        if ($freight_zip == $origin_zip && (isset($freight_package['origin'][$shipment_key], $freight_package['items'][$shipment_key]))) {
                                            unset($freight_package['origin'][$shipment_key]);
                                            unset($freight_package['items'][$shipment_key]);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $smallQuotes = (isset($smallQuotes) && is_array($smallQuotes) && (!empty($smallQuotes))) ? reset($smallQuotes) : $smallQuotes;
                    $smallMinRate = (is_array($smallQuotes) && (!empty($smallQuotes))) ? current($smallQuotes) : $smallQuotes;

                    $quotes = [];
                    $en_package = $web_service_array = $freight_quotes->quotes_request($freight_package, $this->package_plugin);
                    $quotes = $freight_quotes->get_quotes($web_service_array);

                    // Backup rates
                    if ((empty($quotes) && get_option('gtz_ltl_backup_rates_carrier_returns_error') == 'yes') || (is_array($quotes) && isset($quotes['error']) && $quotes['error'] == 'backup_rate' && get_option('gtz_ltl_backup_rates_carrier_fails_to_return_response') == 'yes')) {
                        $this->gtz_ltl_backup_rates();
                        return [];
                    }

                    if (empty($quotes)) {
                        return [];
                    }
                    // Eniture debug mood
                    do_action("eniture_debug_mood", "Quotes Response (get_quotes CERASIS)", $quotes);

                    // Virtual products
                    $virtual_rate = $this->en_virtual_products();

                    // FDO
                    if (isset($smallMinRate['meta_data']['en_fdo_meta_data'])) {
                        if (!empty($smallMinRate['meta_data']['en_fdo_meta_data']) && !is_array($smallMinRate['meta_data']['en_fdo_meta_data'])) {
                            $en_third_party_fdo_meta_data = json_decode($smallMinRate['meta_data']['en_fdo_meta_data'], true);
                            isset($en_third_party_fdo_meta_data['data']) ? $smallMinRate['meta_data']['en_fdo_meta_data'] = $en_third_party_fdo_meta_data['data'] : '';
                        }

                        $this->en_fdo_meta_data_third_party = (isset($smallMinRate['meta_data']['en_fdo_meta_data']['address'])) ? [$smallMinRate['meta_data']['en_fdo_meta_data']] : $smallMinRate['meta_data']['en_fdo_meta_data'];
                    }

                    $smpkgCost = (isset($smallMinRate['cost'])) ? $smallMinRate['cost'] : 0;

                    $this->InstorPickupLocalDelivery = $freight_quotes->return_cerasis_localdelivery_array();
                    
                    $this->quote_settings = $this->cerasis_res_inst->quote_settings;
                    $this->quote_settings = json_decode(json_encode($this->quote_settings), true);

                    // Eniture debug mood
                    do_action("eniture_debug_mood", "Quote Settings (CERASIS)", $this->quote_settings);

                    $quotes = json_decode(json_encode($quotes), true);
                    $cerasis_global_tranz_api_endpoint = get_option('cerasis_global_tranz_api_endpoint');

                    // Legacy / New API quotes compilation
                    if ($cerasis_global_tranz_api_endpoint == 'wc_global_tranz_api_fields' || $cerasis_global_tranz_api_endpoint == 'wc_global_tranz_new_api_fields') {
                        $en_rates = \EnFreightviewResponse\EnFreightviewResponse::en_rates($quotes, $en_package, $smallMinRate, $this->quote_settings);
                        
                        // Check and remove liftgate quotes if not available in all the shipments
                        $en_accessorial_excluded = apply_filters('en_gtz_ltl_accessorial_excluded', []);
                        if (!empty($en_accessorial_excluded) && in_array('liftgateResidentialExcluded', $en_accessorial_excluded)) {
                            foreach ($en_rates as $key => $value) {
                                if (is_string($key) && strpos($key, 'L') !== false) unset($en_rates[$key]);
                            }
                        }

                        $accessorials = [
                            'R' => 'residential delivery',
                            'L' => 'liftgate delivery',
                            'T' => 'tailgate delivery',
                            'N' => 'notify before delivery',
                            'A' => 'limited access delivery'
                        ];
                        $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
                        if (stripos(implode($all_plugins), 'residential-address-detection.php') || is_plugin_active_for_network('residential-address-detection/residential-address-detection.php')) {
                            $rad_status = get_option('residential_delivery_options_disclosure_types_to') != 'not_show_r_checkout';
                            if($rad_status == false && get_option('suspend_automatic_detection_of_residential_addresses') != 'yes') {
                                unset($accessorials['R']);
                            }
                        }
                        // Eniture Debug Mood
                        do_action("eniture_debug_mood", "GlobalTranz Rates ", $en_rates);

                        // Images for FDO
                        $image_urls = apply_filters('en_fdo_image_urls_merge', []);
                        foreach ($en_rates as $accessorial => $rate) {
                            if (isset($rate['label_sufex']) && !empty($rate['label_sufex'])) {

                                if (isset($rate['cost']) && $rate['cost'] <= 0) {
                                    unset($en_rates[$accessorial]);
                                    continue;
                                }

                                $label_sufex = array_intersect_key($accessorials, array_flip($rate['label_sufex']));
                                $rate['label'] = isset($rate['label']) ? $rate['label'] : $rate['meta_data']['service_type'];
                                $rate['label'] .= (!empty($label_sufex)) ? ' with ' . implode(' and ', $label_sufex) : '';
                                (isset($rate['cutoff_time']) && strlen($rate['cutoff_time']) > 0) ? $rate['label'] .= $rate['cutoff_time'] : '';
                                isset($rate['cost']) && $smpkgCost > 0 ?
                                    $rate['cost'] = $rate['cost'] + $smpkgCost : 0;
                                // Order widget detail set
                                $en_virtual_fdo_meta_data = [];
                                if (isset($rate['min_prices'], $rate['en_fdo_meta_data'])) {
                                    $en_fdo_meta_data = $rate['en_fdo_meta_data'];
                                    (!empty($this->en_fdo_meta_data_third_party)) ? $en_fdo_meta_data = array_merge($en_fdo_meta_data, $this->en_fdo_meta_data_third_party) : '';
                                    // Virtual products
                                    if (!empty($virtual_rate) && isset($virtual_rate['meta_data'], $virtual_rate['meta_data']['en_fdo_meta_data'])) {
                                        $en_virtual_fdo_meta_data[] = $virtual_rate['meta_data']['en_fdo_meta_data'];
                                        $en_fdo_meta_data = array_merge($en_fdo_meta_data, $en_virtual_fdo_meta_data);
                                    }

                                    $rate['min_prices'] = !empty($en_small_package_quotes) ? array_merge($rate['min_prices'], $en_small_package_quotes) : $rate['min_prices'];
                                    $rate['meta_data']['min_prices'] = wp_json_encode($rate['min_prices']);
                                    $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode(['data' => $en_fdo_meta_data, 'shipment' => 'multiple']);
                                    unset($rate['min_prices']);
                                } else {
                                    $en_fdo_meta_data = (isset($rate['meta_data']['en_fdo_meta_data'])) ? [$rate['meta_data']['en_fdo_meta_data']] : [];
                                    // Virtual products
                                    if (!empty($virtual_rate) && isset($virtual_rate['meta_data'], $virtual_rate['meta_data']['en_fdo_meta_data'])) {
                                        $en_virtual_fdo_meta_data[] = $virtual_rate['meta_data']['en_fdo_meta_data'];
                                        $en_fdo_meta_data = array_merge($en_fdo_meta_data, $en_virtual_fdo_meta_data);
                                    }

                                    $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode(['data' => $en_fdo_meta_data, 'shipment' => 'single']);
                                }

                                // Images for FDO
                                $rate['meta_data']['en_fdo_image_urls'] = wp_json_encode($image_urls);
                                $rate['id'] = isset($rate['id']) && is_string($rate['id']) ? $this->id . ':' . $rate['id'] : '';
                            }

                            $this->add_rate($rate);
                            $en_rates[$accessorial] = $rate;
                        }

                        return $en_rates;
                    }

                    $handling_fee = $this->quote_settings['handling_fee'];
                    $rates = [];

                    // Set origin and product level markup indexes
                    $quotes = $this->setOriginAndProductMarkups($quotes, $en_package);
                    $Cerasis_Quotes = new Engtz_Cerasis_Quotes();
                    
                    // apply override rates shipping rules
                    $shipping_rule_obj = new EnGtzLtlShippingRulesAjaxReq();
                    if (!empty($quotes)) {
                        foreach ($quotes as $key => $quote) {
                           $quotes[$key] = $shipping_rule_obj->apply_shipping_rules($en_package, true, $quote, $key);
                        }
                    }

                    // Cerasis multi and single shipment quotes compilation
                    if (count((array)$quotes) > 1 || $smpkgCost > 0 || !empty($virtual_rate)) {

                        $liftgate_count = $non_liftgate_count = $multi_cost = $s_multi_cost = 0;
                        $_label = "";

                        // Custom client work "ltl_remove_small_minimum_value_By_zero_when_coupon_add"
                        if (has_filter('small_min_remove_zero_type_params')) {
                            $smpkgCost = apply_filters('small_min_remove_zero_type_params', $package, $smpkgCost);
                        }

                        $this->quote_settings['shipment'] = "multi_shipment";
                        (isset($small_quotes) && count($small_quotes) > 0) ? $this->minPrices['LIFT'] = $small_quotes : "";
                        (isset($small_quotes) && count($small_quotes) > 0) ? $this->minPrices['NOTLIFT'] = $small_quotes : "";

                        // Virtual products
                        if (!empty($virtual_rate)) {
                            $en_virtual_fdo_meta_data[] = $virtual_rate['meta_data']['en_fdo_meta_data'];
                            $virtual_meta_rate['virtual_rate'] = $virtual_rate;
                            $this->minPrices['LIFT'] = isset($this->minPrices['LIFT']) && !empty($this->minPrices['LIFT']) ? array_merge($this->minPrices['LIFT'], $virtual_meta_rate) : $virtual_meta_rate;
                            $this->minPrices['NOTLIFT'] = isset($this->minPrices['NOTLIFT']) && !empty($this->minPrices['NOTLIFT']) ? array_merge($this->minPrices['NOTLIFT'], $virtual_meta_rate) : $virtual_meta_rate;

                            $this->en_fdo_meta_data_third_party = !empty($this->en_fdo_meta_data_third_party) ? array_merge($this->en_fdo_meta_data_third_party, $en_virtual_fdo_meta_data) : $en_virtual_fdo_meta_data;
                        }

                        foreach ($quotes as $key => $quote) {
                            $meta_data = (isset($web_service_array['meta_data'][$key])) ? $web_service_array['meta_data'][$key] : [];
                            $key = "LTL_" . $key;

                            $quote = $freight_quotes->pass_quotes($quote, $cart_obj, $handlng_fee, $meta_data);

                            $simple_quotes = (isset($quote['simple_quotes'])) ? $quote['simple_quotes'] : array();
                            $quote = $this->remove_array($quote, 'simple_quotes');

                            $rates = $Cerasis_Quotes->calculate_quotes($quote, $this->quote_settings);

                            $rates = reset($rates);
                            $this->minPrices['LIFT'][$key] = $rates;

                            $_cost = (isset($rates['cost'])) ? $rates['cost'] : 0;
                            $_label = (isset($rates['label_sufex'])) ? $rates['label_sufex'] : array();
                            $append_label = (isset($rates['append_label'])) ? $rates['append_label'] : "";

                            if (isset($rates['meta_data']['en_fdo_meta_data']['rate'])) {
                                $fdo_rate = $rates['meta_data']['en_fdo_meta_data']['rate'];
                                (isset($fdo_rate['cost'])) ? $rates['meta_data']['en_fdo_meta_data']['rate']['cost'] = $_cost : '';
                            }

                            // FDO
                            $this->en_fdo_meta_data['LIFT'][$key] = (isset($rates['meta_data']['en_fdo_meta_data'])) ? $rates['meta_data']['en_fdo_meta_data'] : [];

                            $handling_fee = (isset($rates['markup']) && (strlen($rates['markup']) > 0)) ? $rates['markup'] : $handling_fee;

                            // Offer lift gate delivery as an option is enabled
                            if (isset($this->quote_settings['liftgate_delivery_option']) &&
                                ($this->quote_settings['liftgate_delivery_option'] == "yes") &&
                                (!empty($simple_quotes))) {

                                $s_rates = $Cerasis_Quotes->calculate_quotes($simple_quotes, $this->quote_settings);
                                $s_rates = reset($s_rates);

                                $this->minPrices['NOTLIFT'][$key] = $s_rates;

                                $s_cost = (isset($s_rates['cost'])) ? $s_rates['cost'] : 0;
                                $s_label = (isset($s_rates['label_sufex'])) ? $s_rates['label_sufex'] : array();
                                $s_append_label = (isset($s_rates['append_label'])) ? $s_rates['append_label'] : "";

                                if (isset($s_rates['meta_data']['en_fdo_meta_data']['rate'])) {
                                    $fdo_rate = $s_rates['meta_data']['en_fdo_meta_data']['rate'];
                                    (isset($fdo_rate['cost'])) ? $s_rates['meta_data']['en_fdo_meta_data']['rate']['cost'] = $s_cost : '';
                                }

                                // FDO
                                $this->en_fdo_meta_data['NOTLIFT'][$key] = (isset($s_rates['meta_data']['en_fdo_meta_data'])) ? $s_rates['meta_data']['en_fdo_meta_data'] : [];
                                $this->en_fdo_meta_data['NOTLIFT'][$key]['rate']['cost'] = $this->add_handling_fee($s_cost, $handling_fee);

                                $s_multi_cost_fee = $this->add_handling_fee($s_cost, $handling_fee);
                                $s_multi_cost += $s_multi_cost_fee > 0 ? $s_multi_cost_fee : 0;
                                $non_liftgate_count++;
                            }

                            $multi_cost_fees = $this->add_handling_fee($_cost, $handling_fee);
                            $this->en_fdo_meta_data['LIFT'][$key]['rate']['cost'] = $this->add_handling_fee($_cost, $handling_fee);
                            if ($multi_cost_fees > 0) {
                                $multi_cost += $multi_cost_fees;
                                $liftgate_count++;
                            }

                            // Eniture debug mood
                            do_action("eniture_debug_mood", "In Foreach Multi cost (CERASIS)", $multi_cost);
                        }

                        (count((array)$quotes) == $non_liftgate_count && $s_multi_cost > 0) ? $rate[] = $this->arrange_multiship_freight(($s_multi_cost + $smpkgCost), 'NOTLIFT', $s_label, $s_append_label) : "";
                        (count((array)$quotes) == $liftgate_count && $multi_cost > 0) ? $rate[] = $this->arrange_multiship_freight(($multi_cost + $smpkgCost), 'LIFT', $_label, $append_label) : "";

                        $this->shipment_type = 'multiple';

                        // Eniture debug mood
                        $rate = isset($rate) && !empty($rate) ? $rate : [];
                        do_action("eniture_debug_mood", "Multi Rates (CERASIS)", $rate);
                        return $this->cerasis_add_rate_arr($rate);
                    } else {

                        if (isset($quotes) && !empty($quotes)) {
                            $meta_data = [];
                            if (isset($web_service_array['meta_data'])) {
                                $meta_data = reset($web_service_array['meta_data']);
                            }

                            $quote = $freight_quotes->pass_quotes(reset($quotes), $cart_obj, $handlng_fee, $meta_data);
                            $simple_quotes = (isset($quote['simple_quotes'])) ? $quote['simple_quotes'] : array();
                            $quote = $this->remove_array($quote, 'simple_quotes');

                            $rates = $Cerasis_Quotes->calculate_quotes($quote, $this->quote_settings);

                            //                  Offer lift gate delivery as an option is enabled
                            if (isset($this->quote_settings['liftgate_delivery_option']) &&
                                ($this->quote_settings['liftgate_delivery_option'] == "yes") &&
                                (!empty($simple_quotes))) {

                                $simple_rates = $Cerasis_Quotes->calculate_quotes($simple_quotes, $this->quote_settings);

                                $rates = array_merge($rates, $simple_rates);

                            }

                            $cost_sorted_key = array();

                            $this->quote_settings['shipment'] = "single_shipment";

                            foreach ($rates as $key => $quote) {
                                $handling_fee = (isset($rates['markup']) && (strlen($rates['markup']) > 0)) ? $rates['markup'] : $handling_fee;
                                $_cost = (isset($quote['cost'])) ? $quote['cost'] : 0;

                                if (isset($quote['meta_data']['en_fdo_meta_data']['rate'])) {
                                    $fdo_rate = $quote['meta_data']['en_fdo_meta_data']['rate'];
                                    (isset($fdo_rate['cost'])) ? $rates[$key]['meta_data']['en_fdo_meta_data']['rate']['cost'] = $this->add_handling_fee($_cost, $handling_fee) : '';
                                }

                                $rates[$key]['cost'] = $this->add_handling_fee($_cost, $handling_fee);
                                if ($rates[$key]['cost'] <= 0) {
                                    unset($rates[$key]);
                                    continue;
                                }
                                
                                $cost_sorted_key[$key] = (isset($quote['cost'])) ? $quote['cost'] : 0;
                                $rates[$key]['shipment'] = "single_shipment";

                                /*$this->quote_settings['transit_days'] == "yes" && isset($quote['transit_days']) && strlen($quote['transit_days']) > 0 ? $rates[$key]['transit_label'] = ' ( Estimated transit time of ' . $quote['transit_days'] . ' business days. )' : "";
                                $this->quote_settings['transit_days'] == "yes" && isset($quote['gtz_transit_days']) && strlen($quote['gtz_transit_days']) > 0 ? $rates[$key]['transit_label'] = ' ( Estimated transit time of ' . $quote['gtz_transit_days'] . ' calendar days. )' : "";*/
                            }

                            // array_multisort 
                            array_multisort($cost_sorted_key, SORT_ASC, $rates);

                            $rates = $this->cerasis_add_rate_arr($rates);
                        }

                        if(is_array($this->web_service_inst->en_wd_origin_array)){
                            $origin_array = reset($this->web_service_inst->en_wd_origin_array);
                        }

                        // Origin terminal address
                        (isset($this->InstorPickupLocalDelivery->localDelivery, $origin_array) && ($this->InstorPickupLocalDelivery->localDelivery->status == 1)) ? $this->local_delivery($origin_array['fee_local_delivery'], $origin_array['checkout_desc_local_delivery'], $origin_array) : "";
                        (isset($this->InstorPickupLocalDelivery->inStorePickup, $origin_array, $this->InstorPickupLocalDelivery->totalDistance) && ($this->InstorPickupLocalDelivery->inStorePickup->status == 1)) ? $this->pickup_delivery($origin_array['checkout_desc_store_pickup'], $origin_array, $this->InstorPickupLocalDelivery->totalDistance) : "";

                        $this->shipment_type = 'single';

                        return $rates;
                    }
                }

                /**
                 * Multishipment
                 * @return array
                 */
                function arrange_multiship_freight($cost, $id, $label_sufex, $append_label)
                {

                    return array(
                        'id' => $id,
                        'label' => "Freight",
                        'cost' => $cost,
                        'label_sufex' => $label_sufex,
                        'append_label' => $append_label,
                        'plugin_name' => 'globalTranz',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture'
                    );
                }

                /**
                 * Free Shipping rate
                 * @param $coupon
                 * @return string/array
                 */
                function arrange_own_freight()
                {
                    return array(
                        'id' => $this->id . ':' . 'free',
                        'label' => $this->arrangement_text,
                        'cost' => 0,
                        'plugin_name' => 'globalTranz',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture'
                    );
                }

                /**
                 *
                 * @param string type $price
                 * @param string type $handling_fee
                 * @return float type
                 */
                function add_handling_fee($price, $handling_fee)
                {
                    $handelingFee = 0;
                    if ($handling_fee != '' && $handling_fee != 0) {
                        if (strrchr($handling_fee, "%")) {

                            $prcnt = (float)$handling_fee;
                            $handelingFee = (float)$price / 100 * $prcnt;
                        } else {
                            $handelingFee = (float)$handling_fee;
                        }
                    }

                    $handelingFee = $this->smooth_round($handelingFee);
                    $price = (float)$price + $handelingFee;
                    return $price;
                }

                function en_sort_woocommerce_available_shipping_methods($rates, $package)
                {
                    //  if there are no rates don't do anything
                    if (!$rates) {
                        return;
                    }

                    // get an array of prices
                    $prices = array();
                    foreach ($rates as $rate) {
                        $prices[] = $rate->cost;
                    }

                    // use the prices to sort the rates
                    array_multisort($prices, $rates);

                    // return the rates
                    return $rates;
                }

                /**
                 * Pickup delivery quote
                 * @return array type
                 */
                function pickup_delivery($label, $en_wd_origin_array, $total_distance)
                {
                    $this->woocommerce_package_rates = 1;
                    $this->instore_pickup_and_local_delivery = TRUE;

                    $label = (isset($label) && (strlen($label) > 0)) ? $label : 'In-store pick up';
                    // Origin terminal address
                    $address = (isset($en_wd_origin_array['address'])) ? $en_wd_origin_array['address'] : '';
                    $city = (isset($en_wd_origin_array['city'])) ? $en_wd_origin_array['city'] : '';
                    $state = (isset($en_wd_origin_array['state'])) ? $en_wd_origin_array['state'] : '';
                    $zip = (isset($en_wd_origin_array['zip'])) ? $en_wd_origin_array['zip'] : '';
                    $phone_instore = (isset($en_wd_origin_array['phone_instore'])) ? $en_wd_origin_array['phone_instore'] : '';
                    strlen($total_distance) > 0 ? $label .= ', Free | ' . str_replace("mi", "miles", $total_distance) . ' away' : '';
                    strlen($address) > 0 ? $label .= ' | ' . $address : '';
                    strlen($city) > 0 ? $label .= ', ' . $city : '';
                    strlen($state) > 0 ? $label .= ' ' . $state : '';
                    strlen($zip) > 0 ? $label .= ' ' . $zip : '';
                    strlen($phone_instore) > 0 ? $label .= ' | ' . $phone_instore : '';

                    $pickup_delivery = array(
                        'id' =>$this->id . ':' . 'in-store-pick-up',
                        'cost' => 0,
                        'label' => $label,
                        'plugin_name' => 'globalTranz',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture'
                    );

                    add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                    $this->add_rate($pickup_delivery);
                }

                /**
                 * Local delivery quote
                 * @param string type $cost
                 * @return array type
                 */
                function local_delivery($cost, $label, $en_wd_origin_array)
                {
                    $this->woocommerce_package_rates = 1;
                    $this->instore_pickup_and_local_delivery = TRUE;
                    $label = (isset($label) && (strlen($label) > 0)) ? $label : 'Local Delivery';

                    $local_delivery = array(
                        'id' => $this->id . ':' . 'local-delivery',
                        'cost' => $cost,
                        'label' => $label,
                        'plugin_name' => 'globalTranz',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture'
                    );

                    add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                    $this->add_rate($local_delivery);
                }

                /**
                 * Remove array
                 * @return array
                 */
                function remove_array($quote, $remove_index)
                {
                    unset($quote[$remove_index]);

                    return $quote;
                }

                /**
                 * filter label new update
                 * @param type $label_sufex
                 * @return string
                 */
                public function engtz_filter_from_label_sufexl($label_sufex)
                {
                    $append_label = "";
                    $rad_status = true;
                    $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
                    if (stripos(implode($all_plugins), 'residential-address-detection.php') || is_plugin_active_for_network('residential-address-detection/residential-address-detection.php')) {
                        if(get_option('suspend_automatic_detection_of_residential_addresses') != 'yes') {
                            $rad_status = get_option('residential_delivery_options_disclosure_types_to') != 'not_show_r_checkout';
                        }
                    }
                    switch (TRUE) {
                        case (in_array("R", $label_sufex) && in_array("L", $label_sufex) && $rad_status == true):
                            $append_label = " with lift gate and residential delivery ";
                            break;

                        case (in_array("L", $label_sufex)):
                            $append_label = " with lift gate delivery ";
                            break;

                        case (in_array("R", $label_sufex) && $rad_status == true):
                            $append_label = " with residential delivery ";
                            break;
                    }

                    return $append_label;
                }

                /**
                 *
                 * @param float type $val
                 * @param int type $min
                 * @param int type $max
                 * @return float type
                 */
                function smooth_round($val, $min = 2, $max = 4)
                {
                    $result = round($val, $min);
                    if ($result == 0 && $min < $max) {
                        return $this->smooth_round($val, ++$min, $max);
                    } else {
                        return $result;
                    }
                }

                /**
                 * Label from quote settings tab
                 * @return string type
                 */
                public function wwe_label_as()
                {
                    return (strlen($this->quote_settings['wwe_label']) > 0) ? $this->quote_settings['wwe_label'] : "Freight";
                }

                /**
                 * Append label in quote
                 * @param array type $rate
                 * @return string type
                 */
                public function set_label_in_quote($rate)
                {

                    $rate_label = isset($rate['label']) ? $rate['label'] : '';
                    $cerasis_global_tranz_api_endpoint = get_option('cerasis_global_tranz_api_endpoint');
                    $label_sufex = (isset($rate['label_sufex'])) ? array_unique($rate['label_sufex']) : array();

                    if ($cerasis_global_tranz_api_endpoint == 'wc_global_tranz_api_fields') {
                        $wwe_lowest_label = (strlen($this->quote_settings['wwe_lowest_label']) > 0) ? $this->quote_settings['wwe_lowest_label'] : "";
                        $wwe_quickest_label = (strlen($this->quote_settings['wwe_quickest_label']) > 0) ? $this->quote_settings['wwe_quickest_label'] : "";
                        $carrier_scac = (isset($rate['carrier_scac'])) ? $rate['carrier_scac'] : '';

                        if (!isset($rate['label']) ||
                            ($this->quote_settings['shipment'] == "single_shipment" &&
                                strlen($wwe_lowest_label) > 0 && $carrier_scac == 'LowestCostRate')) {
                            $rate_label = $wwe_lowest_label;
                        }

                        if (!isset($rate['label']) ||
                            ($this->quote_settings['shipment'] == "single_shipment" &&
                                strlen($wwe_quickest_label) > 0 && $carrier_scac == 'QuickestTransitRate')) {
                            $rate_label = $wwe_quickest_label;
                        }

                    } else {
                        $rate_label = (!isset($rate['label']) ||
                            ($this->quote_settings['shipment'] == "single_shipment" &&
                                strlen($this->quote_settings['wwe_label']) > 0)) ?
                            $this->wwe_label_as() : $rate['label'];
                    }

                    $rate_label .= (isset($this->quote_settings['sandbox'])) ? ' (Sandbox) ' : '';

                    $rate_label .= $this->engtz_filter_from_label_sufexl($label_sufex);
                    // Cuttoff Time
                    $delivery_estimate_gt = isset($this->quote_settings['delivery_estimates']) ? $this->quote_settings['delivery_estimates'] : '';
                    $shipment_type = isset($this->quote_settings['shipment']) && !empty($this->quote_settings['shipment']) ? $this->quote_settings['shipment'] : '';
                    if (isset($this->quote_settings['delivery_estimates']) && !empty($this->quote_settings['delivery_estimates'])
                        && $this->quote_settings['delivery_estimates'] != 'dont_show_estimates' && $shipment_type != 'multi_shipment') {
                        if ($this->quote_settings['delivery_estimates'] == 'delivery_date') {
                            isset($rate['delivery_time_stamp']) && is_string($rate['delivery_time_stamp']) && strlen($rate['delivery_time_stamp']) > 0 ? $rate_label .= ' (Expected delivery by ' . date('m-d-Y', strtotime($rate['delivery_time_stamp'])) . ')' : '';
                        } else if ($delivery_estimate_gt == 'delivery_days') {
                            $correct_word = (isset($rate['delivery_estimates']) && $rate['delivery_estimates'] == 1) ? 'is' : 'are';
                            isset($rate['delivery_estimates']) && is_string($rate['delivery_estimates']) && strlen($rate['delivery_estimates']) > 0 ? $rate_label .= ' (Intransit days: ' . $rate['delivery_estimates'] . ')' : '';
                        }
                    }
                    return $rate_label;
                }

                /**
                 * rates to add_rate woocommerce
                 * @param array type $add_rate_arr
                 */
                public function cerasis_add_rate_arr($add_rate_arr)
                {
                    if (isset($add_rate_arr) && (!empty($add_rate_arr)) && (is_array($add_rate_arr))) {
                        add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                        $instore_pickup_local_devlivery_action = apply_filters('globaltranz_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');

                        // Images for FDO
                        $image_urls = apply_filters('en_fdo_image_urls_merge', []);

                        foreach ($add_rate_arr as $key => $rate) {

                            $rate['label'] = $this->set_label_in_quote($rate);
                            if (isset($rate['meta_data']['en_fdo_meta_data']['rate']['label'])) {
                                $rate['meta_data']['en_fdo_meta_data']['rate']['label'] = $rate['label'];
                            }

                            if (isset($rate['meta_data'])) {
                                $rate['meta_data']['label_sufex'] = (isset($rate['label_sufex'])) ? json_encode($rate['label_sufex']) : array();
                            }

                            $rate['id'] = (isset($rate['id'])) ? $rate['id'] : '';

                            if (isset($this->minPrices[$rate['id']])) {
                                $rate['meta_data']['min_prices'] = json_encode($this->minPrices[$rate['id']]);
                                $rate['meta_data']['en_fdo_meta_data']['data'] = array_values($this->en_fdo_meta_data[$rate['id']]);
                                (!empty($this->en_fdo_meta_data_third_party)) ? $rate['meta_data']['en_fdo_meta_data']['data'] = array_merge($rate['meta_data']['en_fdo_meta_data']['data'], $this->en_fdo_meta_data_third_party) : '';
                                $rate['meta_data']['en_fdo_meta_data']['shipment'] = 'multiple';
                                $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode($rate['meta_data']['en_fdo_meta_data']);
                            } else {
                                $en_set_fdo_meta_data['data'] = isset($rate['meta_data']['en_fdo_meta_data']) ? [$rate['meta_data']['en_fdo_meta_data']] : [];
                                $en_set_fdo_meta_data['shipment'] = 'sinlge';
                                $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode($en_set_fdo_meta_data);
                            }

                            // Images for FDO
                            $rate['meta_data']['en_fdo_image_urls'] = wp_json_encode($image_urls);
                            $rate['id'] = isset($rate['id']) && is_string($rate['id']) ? $this->id . ':' . $rate['id'] : '';

                            if(is_array($this->web_service_inst->en_wd_origin_array)){
                                $origin_array = reset($this->web_service_inst->en_wd_origin_array);
                            }

                            if (isset($origin_array, $origin_array['suppress_local_delivery']) && $origin_array['suppress_local_delivery'] == "1" && (!is_array($instore_pickup_local_devlivery_action))) {
                                $rate = apply_filters('suppress_local_delivery', $rate, $this->web_service_inst->en_wd_origin_array, $this->package_plugin, $this->InstorPickupLocalDelivery);
                                if (!empty($rate)) {
                                    $this->add_rate($rate);
                                    $this->woocommerce_package_rates = 1;
                                }
                            } else {
                                if (isset($rate['cost']) && $rate['cost'] > 0) {
                                    $this->add_rate($rate);
                                }
                            }

                            $add_rate_arr[$key] = $rate;
                        }

                        (isset($this->quote_settings['own_freight']) && ($this->quote_settings['own_freight'] == "yes")) ? $this->add_rate($this->arrange_own_freight()) : "";

                        // Eniture debug mood
                        do_action("eniture_debug_mood", "Final Quotes (CERASIS)", $add_rate_arr);

                        return $add_rate_arr;
                    }
                }

                /**
                 * quote settings array
                 * @global $wpdb $wpdb
                 */
                function ltl_shipping_quote_settings()
                {
                    global $wpdb;

                    $rating_method = get_option('wc_settings_cerasis_rate_method');
                    $cerasis_global_tranz_shipping_service = get_option('cerasis_global_tranz_shipping_service');
                    $wwe_label = get_option('wc_settings_cerasis_label_as');
                    // $this->cerasis_res_inst->quote_settings['transit_days'] = get_option('wc_settings_cerasis_delivery_estimate');
                    $this->cerasis_res_inst->quote_settings['own_freight'] = get_option('wc_settings_cerasis_allow_for_own_arrangment');
                    $this->cerasis_res_inst->quote_settings['own_freight_label'] = get_option('wc_settings_cerasis_text_for_own_arrangment');
                    $this->cerasis_res_inst->quote_settings['total_carriers'] = get_option('wc_settings_cerasis_Number_of_options');
                    $this->cerasis_res_inst->quote_settings['rating_method'] = (isset($rating_method) && (strlen($rating_method)) > 0) ? $rating_method : "Cheapest";
                    $this->cerasis_res_inst->quote_settings['wwe_label'] = (($rating_method == "average_rate" || $rating_method == "Cheapest") && $cerasis_global_tranz_shipping_service != 'wc_final_mile_service') ? $wwe_label : "";
                    $this->cerasis_res_inst->quote_settings['en_settings_label'] = (($rating_method == "average_rate" || $rating_method == "Cheapest") && $cerasis_global_tranz_shipping_service != 'wc_final_mile_service') ? $wwe_label : "";

                    $this->cerasis_res_inst->quote_settings['wwe_lowest_label'] = get_option('wc_settings_globaltranz_lowest_label_as');
                    $this->cerasis_res_inst->quote_settings['wwe_quickest_label'] = get_option('wc_settings_globaltranz_quickest_label_as');

                    $this->cerasis_res_inst->quote_settings['handling_fee'] = get_option('wc_settings_cerasis_hand_free_mark_up');
                    $this->cerasis_res_inst->quote_settings['liftgate_delivery'] = get_option('wc_settings_cerasis_lift_gate_delivery');
                    $this->cerasis_res_inst->quote_settings['liftgate_delivery_option'] = get_option('cerasis_freights_liftgate_delivery_as_option');
                    $this->cerasis_res_inst->quote_settings['residential_delivery'] = get_option('wc_settings_cerasis_residential_delivery');
                    $this->cerasis_res_inst->quote_settings['liftgate_resid_delivery'] = get_option('en_woo_addons_liftgate_with_auto_residential');

                    $this->cerasis_res_inst->quote_settings['notify_delivery'] = get_option('wc_settings_cerasis_notify');
                    $this->cerasis_res_inst->quote_settings['notify_delivery_option'] = get_option('cerasis_freights_notify_as_option');

                    $this->cerasis_res_inst->quote_settings['limited_access_delivery'] = get_option('gtz_limited_access_delivery');
                    $this->cerasis_res_inst->quote_settings['limited_access_delivery_option'] = get_option('gtz_limited_access_delivery_as_option');
                    $this->cerasis_res_inst->quote_settings['limited_access_delivery_fee'] = get_option('gtz_limited_access_delivery_fee');

                    // Cuttoff Time
                    $this->cerasis_res_inst->quote_settings['delivery_estimates'] = get_option('gt_delivery_estimates');
                    $this->cerasis_res_inst->quote_settings['orderCutoffTime'] = get_option('gt_freight_order_cut_off_time');
                    $this->cerasis_res_inst->quote_settings['shipmentOffsetDays'] = get_option('gt_freight_shipment_offset_days');
                    $this->cerasis_res_inst->quote_settings['fm_services'] = $this->fm_services();
                    $this->cerasis_res_inst->quote_settings['handling_weight'] = get_option('engtz_freight_handling_weight');
                    $this->cerasis_res_inst->quote_settings['maximum_handling_weight'] = get_option('engtz_freight_maximum_handling_weight');

                }

                /**
                 * Checking Final Services
                 * @return array
                 */
                function fm_services()
                {
                    $services = '';
                    $en_cerasis_threshold_checkbox = get_option('en_cerasis_threshold_checkbox');
                    $en_cerasis_room_of_choice_checkbox = get_option('en_cerasis_room_of_choice_checkbox');
                    $en_cerasis_premium_checkbox = get_option('en_cerasis_premium_checkbox');

                    $cerasis_global_tranz_shipping_service = get_option('cerasis_global_tranz_shipping_service');
                    if ($cerasis_global_tranz_shipping_service == 'wc_final_mile_service') {
                        ($en_cerasis_threshold_checkbox == 'yes') ? $services = 'THRSHLD_FM' : "";
                        ($en_cerasis_room_of_choice_checkbox == 'yes') ? $services = 'ROOMCHC_FM' : "";
                        ($en_cerasis_premium_checkbox == 'yes') ? $services = 'PREMIUM_FM' : "";
                    }

                    return $services;
                }

                /**
                 * Discard the product which has error
                 */
                public function en_cerasis_discard_defective_product($quotes)
                {
                    foreach ($quotes as $key => $value) {
                        if (isset($value->severity)) {
                            unset($quotes->$key);
                        }
                    }

                    return $quotes;
                }

                /**
                 * Free Shipping rate
                 * @param $coupon
                 * @return string/array
                 */
                function cerasis_shipping_rate_coupon($coupon)
                {
                    foreach ($coupon as $key => $value) {
                        if ($value->get_free_shipping() == 1) {
                            $rates = array(
                                'id' => $this->id . ':' . 'free',
                                'label' => 'Free Shipping',
                                'cost' => 0,
                                'plugin_name' => 'globalTranz',
                                'plugin_type' => 'ltl',
                                'owned_by' => 'eniture'
                            );
                            $this->add_rate($rates);
                            return 'y';
                        }
                    }
                    return 'n';
                }

                /**
                 * Final Rate Array
                 * @param $grand_total
                 * @param $code
                 * @param $label
                 * @return array
                 */
                function cerasis_final_rate_array($grand_total, $code, $label)
                {
                    if ($grand_total > 0) {
                        $rates = array(
                            'id' => $code,
                            'label' => ($label == '') ? 'Freight' : $label,
                            'cost' => $grand_total,
                            'plugin_name' => 'globalTranz',
                            'plugin_type' => 'ltl',
                            'owned_by' => 'eniture'
                        );
                    }
                    return $rates;
                }

                function setOriginAndProductMarkups($quotes, $en_package)
                {
                    foreach ($quotes as $key => $quote) {
                        if (!empty($quote['q']) && isset($en_package['origin_markup'][$key])) {
                            $quotes[$key]['origin_markup'] = $en_package['origin_markup'][$key];
                        }

                        if (!empty($quote['q']) && isset($en_package['product_level_markup'][$key])) {
                            $quotes[$key]['product_level_markup'] = $en_package['product_level_markup'][$key];
                        }
                    }

                    return $quotes;
                }

                /**
                * Adds backup rates in the shipping rates
                * @return void
                * */
                function gtz_ltl_backup_rates()
                {
                    if (get_option('enable_backup_rates_gtz_ltl') != 'yes' || (get_option('gtz_ltl_backup_rates_carrier_fails_to_return_response') != 'yes' && get_option('gtz_ltl_backup_rates_carrier_returns_error') != 'yes')) return;

                    $backup_rates_type = get_option('gtz_ltl_backup_rates_category');
                    $backup_rates_cost = 0;

                    if ($backup_rates_type == 'fixed_rate' && !empty(get_option('gtz_ltl_backup_rates_fixed_rate'))) {
                        $backup_rates_cost = get_option('gtz_ltl_backup_rates_fixed_rate');
                    } elseif ($backup_rates_type == 'percentage_of_cart_price' && !empty(get_option('gtz_ltl_backup_rates_cart_price_percentage'))) {
                        $cart_price_percentage = floatval(str_replace('%', '', get_option('gtz_ltl_backup_rates_cart_price_percentage')));
                        $backup_rates_cost = ($cart_price_percentage * WC()->cart->get_subtotal()) / 100;
                    } elseif ($backup_rates_type == 'function_of_weight' && !empty(get_option('gtz_ltl_backup_rates_weight_function'))) {
                        $cart_weight = wc_get_weight(WC()->cart->get_cart_contents_weight(), 'lbs');
                        $backup_rates_cost = get_option('gtz_ltl_backup_rates_weight_function') * $cart_weight;
                    }

                    if ($backup_rates_cost > 0) {
                        $backup_rates = array(
                            'id' => $this->id . ':' . 'backup_rates',
                            'label' => get_option('gtz_ltl_backup_rates_label'),
                            'cost' => $backup_rates_cost,
                            'plugin_name' => 'globalTranz',
                            'plugin_type' => 'ltl',
                            'owned_by' => 'eniture'
                        );

                        $this->add_rate($backup_rates);
                    }
                }

            }

        }
    }

}
