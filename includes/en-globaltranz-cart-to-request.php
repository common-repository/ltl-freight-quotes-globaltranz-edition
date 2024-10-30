<?php

/**
 * Cart Request Class | cart items requests
 * @package     Woocommerce GlobalTranz Edition
 * @author      <https://eniture.com/>
 * @version     v.1..0 (01/10/2017)
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cart to Request Class | request for carriers, receiver address
 */
if (!class_exists('Engtz_Cart_To_Request')) {

    class Engtz_Cart_To_Request extends Engtz_GlobalTranz_Ltl
    {
        public $hasLTLShipment = 0;
        public $ValidShipmentsArr = [];
        // Images for FDO
        public $en_fdo_image_urls = [];
        // Micro Warehouse
        public $products = [];
        public $dropship_location_array = [];
        public $warehouse_products = [];
        public $destination_Address_gtz;
        public $origin;

        /**
         * variable to display errors
         * @var svariabletring
         */
        public $errors = [];

        /**
         * Cart To Request
         * @param $package
         * @param $freight_quotes
         * @param $freight_zipcode
         * @return array
         */
        function cart_to_request($package, $freight_quotes, $freight_zipcode)
        {
            $cerasis_package = [];
            if (empty($freight_zipcode) || count((array)$package) < 0) {
                return [];
            }
            $weight = 0;
            $dimensions = 0;
            $freight_enable = false;
            $exceedWeight = get_option('en_plugins_return_LTL_quotes');

            $wc_settings_wwe_ignore_items = get_option("en_ignore_items_through_freight_classification");
            $en_get_current_classes = strlen($wc_settings_wwe_ignore_items) > 0 ? trim(strtolower($wc_settings_wwe_ignore_items)) : '';
            $en_get_current_classes_arr = strlen($en_get_current_classes) > 0 ? array_map('trim', explode(',', $en_get_current_classes)) : [];

            // Micro Warehouse
            $smallPluginExist = 0;
            $cerasis_package = $items = $items_shipment = [];
            $engtz_get_shipping_quotes = new Engtz_Quotes_Request();
            $this->destination_Address_gtz = $engtz_get_shipping_quotes->destinationAddressCerasis();
            // Threshold
            $weight_threshold = get_option('en_weight_threshold_lfq');
            $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;

            $flat_rate_shipping_addon = apply_filters('en_add_flat_rate_shipping_addon', false);
            foreach ($package['contents'] as $item_id => $values) {
                $_product = $values['data'];

                // Images for FDO
                $this->en_fdo_image_urls($values, $_product);

                // Flat rate pricing
                $post_id = $product_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
                $parent_id = $post_id;
                if (isset($values['variation_id']) && $values['variation_id'] > 0) {
                    $variation = wc_get_product($values['variation_id']);
                    $parent_id = $variation->get_parent_id();
                }
                $en_flat_rate_price = $this->en_get_flat_rate_price($values, $_product);
                if ($flat_rate_shipping_addon && isset($en_flat_rate_price) && strlen($en_flat_rate_price) > 0) {
                    continue;
                }

                // Get product shipping class
                $en_ship_class = strtolower($values['data']->get_shipping_class());
                if (in_array($en_ship_class, $en_get_current_classes_arr)) {
                    continue;
                }

                // Shippable handling units
                $values = apply_filters('en_shippable_handling_units_request', $values, $values, $_product);
                $shippable = [];
                if (isset($values['shippable']) && !empty($values['shippable'])) {
                    $shippable = $values['shippable'];
                }

                $nestedPercentage = 0;
                $nestedDimension = "";
                $nestedItems = "";
                $StakingProperty = "";
                $height = is_numeric($_product->get_height()) ? $_product->get_height() : 0;
                $width = is_numeric($_product->get_width()) ? $_product->get_width() : 0;
                $length = is_numeric($_product->get_length()) ? $_product->get_length() : 0;
                $height = wc_get_dimension($height, 'in');
                $width = wc_get_dimension($width, 'in');
                $length = wc_get_dimension($length, 'in');
                $product_weight = wc_get_weight($_product->get_weight(), 'lbs');
                $shipping_class_id = $_product->get_shipping_class_id();

                $shipping_class = $_product->get_shipping_class();
                $freight_class = ($shipping_class == 'ltl_freight') ? 'ltl' : '';
                $location_zip = 0;
                $origin_address = $this->get_origin($_product, $values);
                $origin_address = $freight_quotes->get_warehouse($origin_address, $freight_zipcode);

                $freight_class_ltl_gross = $this->get_freight_class($_product, $values['variation_id'], $values['product_id']);
                ($freight_class_ltl_gross == 'Null') ? $freight_class_ltl_gross = "" : "";

                if (!empty($origin_address)) {

                    // Micro Warehouse
                    $this->products[] = $post_id;

                    // Nested Material
                    $nested_material = $this->en_nested_material($values, $_product);

                    if ($nested_material == "yes") {
                        $nestedPercentage = get_post_meta($post_id, '_nestedPercentage', true);
                        $nestedDimension = get_post_meta($post_id, '_nestedDimension', true);
                        $nestedItems = get_post_meta($post_id, '_maxNestedItems', true);
                        $StakingProperty = get_post_meta($post_id, '_nestedStakingProperty', true);
                    }

                    $location_zip = isset($origin_address['zip']) ? trim($origin_address['zip']) : '';
                    $cerasis_package['origin'][$product_id] = $origin_address;
                    $hazardous_material = $this->item_hazmet($_product->get_id());
                    $stackable = $this->item_stackable($_product->get_id());

                    if (!$_product->is_virtual()) {

                        $en_items = $this->item_dimensions($_product, $values, $freight_class_ltl_gross, $freight_class, $height, $width, $length, $product_weight, $nested_material, $nestedPercentage, $nestedDimension, $nestedItems, $StakingProperty, $shippable);

                        // Micro Warehouse
                        $items[$post_id] = $en_items;

                        $cerasis_package['items'][$post_id] = $en_items;

                        // Hazardous Material
                        if ($hazardous_material == "Y" && !isset($cerasis_package['hazardous_material'])) {
                            $cerasis_package['hazardousMaterial'] = TRUE;
                        }
                        // stackable option
                        if ($stackable == "Y" && !isset($cerasis_package['isStackableLineItem'])) {
                            $cerasis_package['isStackableLineItem'] = TRUE;
                        }

                        // Product tags
                        $product_tags = get_the_terms($product_id, 'product_tag');
                        $product_tags = empty($product_tags) ? get_the_terms($parent_id, 'product_tag') : $product_tags;
                        if (!empty($product_tags)) {
                            $product_tag_names = array_map(function($tag) { return $tag->term_id; }, $product_tags);

                            if (isset($cerasis_package['product_tags'][$location_zip]['cerasis'])) {
                                $cerasis_package['product_tags'][$location_zip]['cerasis'] = array_merge($cerasis_package['product_tags'][$location_zip]['cerasis'], $product_tag_names);
                            } else {
                                $cerasis_package['product_tags'][$location_zip]['cerasis'] = $product_tag_names;
                            }
                        } else {
                            $cerasis_package['product_tags'][$location_zip]['cerasis'] = [];
                        }

                        // Product quantity
                        if (isset($cerasis_package['product_quantities'][$location_zip]['cerasis'])) {
                            $cerasis_package['product_quantities'][$location_zip]['cerasis'] += floatval($values['quantity']);
                        } else {
                            $cerasis_package['product_quantities'][$location_zip]['cerasis'] = floatval($values['quantity']);
                        }

                        // Product price
                        if (isset($cerasis_package['product_prices'][$location_zip]['cerasis'])) {
                            $cerasis_package['product_prices'][$location_zip]['cerasis'] += (floatval($_product->get_price()) * floatval($values['quantity']));
                        } else {
                            $cerasis_package['product_prices'][$location_zip]['cerasis'] = (floatval($_product->get_price()) * floatval($values['quantity']));
                        }
                    }
                }

                $freight_enable = $this->get_cerasis_enable($_product);
                $weight += ($product_weight * $values['quantity']);

                // Micro Warehouse
                $items_shipment[$post_id] = $freight_enable;

                // Shipment Weight
                $shipment_weight = $product_weight * $values['quantity'];
                isset($cerasis_package['shipment_weight'][$location_zip]['cerasis']) ? $cerasis_package['shipment_weight'][$location_zip]['cerasis'] += $shipment_weight : $cerasis_package['shipment_weight'][$location_zip]['cerasis'] = $shipment_weight;

                $smallPluginExist = 0;
                $calledMethod = [];
                $eniturePluigns = json_decode(get_option('EN_Plugins'));

                if (!empty($eniturePluigns)) {
                    foreach ($eniturePluigns as $enIndex => $enPlugin) {
                        $freightSmallClassName = 'WC_' . $enPlugin;

                        if (!in_array($freightSmallClassName, $calledMethod)) {
                            if (class_exists($freightSmallClassName)) {
                                $smallPluginExist = 1;
                            }
                            $calledMethod[] = $freightSmallClassName;
                        }
                    }
                }

                if ($freight_enable == true || ($weight > $weight_threshold && $exceedWeight == 'yes')) {
                    add_filter('engtz_triggered', [$this, 'engtz_triggered']);
                    if(isset($cerasis_package['shipment_type'][$location_zip]['small']) && $cerasis_package['shipment_type'][$location_zip]['small'] == 1){
                        unset($cerasis_package['shipment_type'][$location_zip]['small']);
                    }
                    $cerasis_package['shipment_type'][$location_zip]['cerasis'] = 1;
                    $this->hasLTLShipment = 1;
                    $this->ValidShipmentsArr[] = "ltl_freight";
                } elseif (isset($cerasis_package['shipment_type'][$location_zip]['cerasis'])) {
                    $cerasis_package['shipment_type'][$location_zip]['cerasis'] = 1;
                    $this->hasLTLShipment = 1;
                    $this->ValidShipmentsArr[] = "ltl_freight";
                } elseif ($smallPluginExist == 1 && !$cerasis_package['shipment_type'][$location_zip]['cerasis']) {
                    $cerasis_package['shipment_type'][$location_zip]['small'] = 1;
                    $this->ValidShipmentsArr[] = "small_shipment";
                } else {
                    $this->ValidShipmentsArr[] = "no_shipment";
                }
            }

            // Micro Warehouse
            $eniureLicenceKey = get_option('wc_settings_cerasis_licence_key');
            //  Eniture debug mood
            do_action("eniture_debug_mood", "Product Detail (CERASIS)", $cerasis_package);
            return $cerasis_package;
        }

        public function engtz_triggered()
        {
            return true;
        }

        /**
         * Set images urls | Images for FDO
         * @param array type $en_fdo_image_urls
         * @return array type
         */
        public function en_fdo_image_urls_merge($en_fdo_image_urls)
        {
            return array_merge($this->en_fdo_image_urls, $en_fdo_image_urls);
        }

        /**
         * Get images urls | Images for FDO
         * @param array type $values
         * @param array type $_product
         * @return array type
         */
        public function en_fdo_image_urls($values, $_product)
        {
            $product_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
            $gallery_image_ids = $_product->get_gallery_image_ids();
            foreach ($gallery_image_ids as $key => $image_id) {
                $gallery_image_ids[$key] = $image_id > 0 ? wp_get_attachment_url($image_id) : '';
            }

            $image_id = $_product->get_image_id();
            $this->en_fdo_image_urls[$product_id] = [
                'product_id' => $product_id,
                'image_id' => $image_id > 0 ? wp_get_attachment_url($image_id) : '',
                'gallery_image_ids' => $gallery_image_ids
            ];

            add_filter('en_fdo_image_urls_merge', [$this, 'en_fdo_image_urls_merge'], 10, 1);
        }

        /**
         * Nested Material
         * @param array type $values
         * @param array type $_product
         * @return string type
         */
        function en_nested_material($values, $_product)
        {
            $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
            return get_post_meta($post_id, '_nestedMaterials', true);
        }

        /**
         * Check hazmat item
         */
        function item_hazmet($product_id)
        {
            $hazardous_material = apply_filters('globaltranz_quotes_plans_suscription_and_features', 'hazardous_material');
            $enable_hazmet = get_post_meta($product_id, '_hazardousmaterials', true);
            if (!is_array($hazardous_material) && $enable_hazmet == "yes") {
                $hazmet = 'Y';
            } else {
                $hazmet = 'N';
            }

            return $hazmet;
        }

        /**
         * Check stackable item
         */
        function item_stackable($product_id)
        {

            $enable_stackable_option = get_post_meta($product_id, '_stackable', true);

            if ($enable_stackable_option == "yes") {
                $stackable = 'Y';
            } else {
                $stackable = 'N';
            }

            return $stackable;
        }

        /**
         * Product dimensions from cart
         * @param $_product
         * @param $values
         * @param $freight_class_value
         * @param $freight_class
         * @return array
         */

        function item_dimensions($_product, $values, $freight_class_value, $freight_class, $height, $width, $length, $product_weight, $nested_material, $nestedPercentage, $nestedDimension, $nestedItems, $StakingProperty, $shippable)
        {
            $parent_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
            if(isset($values['variation_id']) && $values['variation_id'] > 0){
                $variation = wc_get_product($values['variation_id']);
                $parent_id = $variation->get_parent_id();
            }
            // Shippable handling units
            $lineItemPalletFlag = $lineItemPackageCode = $isPalletLineItem = $lineItemPackageType = '0';
            extract($shippable);

            // Standard Packaging
            $en_ppp_pallet_product = apply_filters('en_ppp_existence', false);
            $ppp_product_pallet = [];
            $values = apply_filters('en_ppp_request', $values, $values, $_product);
            if (isset($values['ppp']) && !empty($values['ppp'])) {
                $ppp_product_pallet = $values['ppp'];
            }

            $ship_as_own_pallet = $vertical_rotation_for_pallet = 'no';
            if (!$en_ppp_pallet_product) {
                $ppp_product_pallet = [];
            }

            extract($ppp_product_pallet);

            $product_title = str_replace(array("'", '"'), '', $_product->get_title());
            $hazmatItemStatus = $this->item_hazmet($_product->get_id());
            $item_stackable_item_status = $this->item_stackable($_product->get_id());
            $product_level_markup = $this->gtz_ltl_get_product_level_markup($_product, $values['variation_id'], $values['product_id'], $values['quantity']);
            $dimensions = array(
                'productId' => $parent_id,
                'productName' => str_replace(array("'", '"'), '', $_product->get_name()),
                'product_name' => $values['quantity'] . " x " . $product_title,
                'productQty' => $values['quantity'],
                'productPrice' => $_product->get_price(),
                'productWeight' => $product_weight,
                'productLength' => $length,
                'productWidth' => $width,
                'productHeight' => $height,
                'freightClass' => $freight_class,
                'productClass' => $freight_class_value,
                'isHazmatLineItem' => $hazmatItemStatus,
                'isStackableLineItem' => $item_stackable_item_status,
                'hazardousMaterial' => $hazmatItemStatus,
                'hazardous_material' => $hazmatItemStatus,
                'hazmat' => $hazmatItemStatus,
                'productType' => ($_product->get_type() == 'variation') ? 'variant' : 'simple',
                'productSku' => $_product->get_sku(),
                'actualProductPrice' => $_product->get_price(),
                'attributes' => $_product->get_attributes(),
                'variantId' => ($_product->get_type() == 'variation') ? $_product->get_id() : '',
                // Nesting
                'nestedMaterial' => $nested_material,
                'nestedPercentage' => $nestedPercentage,
                'nestedDimension' => $nestedDimension,
                'nestedItems' => $nestedItems,
                'stakingProperty' => $StakingProperty,

                // Shippable handling units
                'isPalletLineItem' => $isPalletLineItem,
                'lineItemPalletFlag' => $lineItemPalletFlag,
                'lineItemPackageCode' => $lineItemPackageCode,
                'lineItemPackageType' => $lineItemPackageType,
                // Standard Packaging
                'ship_as_own_pallet' => $ship_as_own_pallet,
                'vertical_rotation_for_pallet' => $vertical_rotation_for_pallet,
                'markup' => $product_level_markup
            );

            // Hook for flexibility adding to package
            $dimensions = apply_filters('en_group_package', $dimensions, $values, $_product);
            // NMFC Number things
            $dimensions = $this->en_group_package($dimensions, $values, $_product);
            return $dimensions;
        }

        /**
         * get locations list
         * @param $_product
         * @param $values
         * @param $freight_quotes
         * @param $freight_zipcode
         * @return string
         * @global $wpdb
         */
        function get_origin($_product, $values)
        {
            global $wpdb;
            $locations_list = [];
            // UPDATE QUERY In-store pick up
            $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
            $enable_dropship = get_post_meta($post_id, '_enable_dropship', true);
            if ($enable_dropship == 'yes') {
                $get_loc = get_post_meta($post_id, '_dropship_location', true);
                if ($get_loc == '') {
                    // Micro Warehouse
                    $this->warehouse_products[] = $post_id;
                    return array('error' => 'wwe small dp location not found!');
                }

                // Multi Dropship
                $multi_dropship = apply_filters('globaltranz_quotes_plans_suscription_and_features', 'multi_dropship');

                if (is_array($multi_dropship)) {
                    $locations_list = $wpdb->get_results(
                        "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'dropship' LIMIT 1"
                    );
                } else {
                    $get_loc = ($get_loc !== '') ? maybe_unserialize($get_loc) : $get_loc;
                    $get_loc = is_array($get_loc) ? implode(" ', '", $get_loc) : $get_loc;
                    $locations_list = $wpdb->get_results(
                        "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE id IN ('" . $get_loc . "')"
                    );
                }

                // Micro Warehouse
                $this->multiple_dropship_of_prod($locations_list, $post_id);
                $eniture_debug_name = "Dropships";
            }
            if (empty($locations_list)) {

                // Multi Warehouse
                $multi_warehouse = apply_filters('globaltranz_quotes_plans_suscription_and_features', 'multi_warehouse');
                if (is_array($multi_warehouse)) {
                    $locations_list = $wpdb->get_results(
                        "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'warehouse' LIMIT 1"
                    );
                } else {
                    $locations_list = $wpdb->get_results(
                        "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'warehouse'"
                    );
                }

                // Micro Warehouse
                $this->warehouse_products[] = $post_id;
                $eniture_debug_name = "Warehouses";
            }

            do_action("eniture_debug_mood", "Quotes $eniture_debug_name (s)", $locations_list);
            return $locations_list;
        }

        // Micro Warehouse
        public function multiple_dropship_of_prod($locations_list, $post_id)
        {
            $post_id = (string)$post_id;

            foreach ($locations_list as $key => $value) {
                $dropship_data = $this->address_array($value);

                $this->origin["D" . $dropship_data['zip']] = $dropship_data;
                if (!isset($this->dropship_location_array["D" . $dropship_data['zip']]) || !in_array($post_id, $this->dropship_location_array["D" . $dropship_data['zip']])) {
                    $this->dropship_location_array["D" . $dropship_data['zip']][] = $post_id;
                }
            }

        }

        // Micro Warehouse
        public function address_array($value)
        {
            $dropship_data = [];

            $dropship_data['locationId'] = (isset($value->id)) ? $value->id : "";
            $dropship_data['zip'] = (isset($value->zip)) ? $value->zip : "";
            $dropship_data['city'] = (isset($value->city)) ? $value->city : "";
            $dropship_data['state'] = (isset($value->state)) ? $value->state : "";
            // Origin terminal address
            $dropship_data['address'] = (isset($value->address)) ? $value->address : "";
            // Terminal phone number
            $dropship_data['phone_instore'] = (isset($value->phone_instore)) ? $value->phone_instore : "";
            $dropship_data['location'] = (isset($value->location)) ? $value->location : "";
            $dropship_data['country'] = (isset($value->country)) ? $value->country : "";
            $dropship_data['enable_store_pickup'] = (isset($value->enable_store_pickup)) ? $value->enable_store_pickup : "";
            $dropship_data['fee_local_delivery'] = (isset($value->fee_local_delivery)) ? $value->fee_local_delivery : "";
            $dropship_data['suppress_local_delivery'] = (isset($value->suppress_local_delivery)) ? $value->suppress_local_delivery : "";
            $dropship_data['miles_store_pickup'] = (isset($value->miles_store_pickup)) ? $value->miles_store_pickup : "";
            $dropship_data['match_postal_store_pickup'] = (isset($value->match_postal_store_pickup)) ? $value->match_postal_store_pickup : "";
            $dropship_data['checkout_desc_store_pickup'] = (isset($value->checkout_desc_store_pickup)) ? $value->checkout_desc_store_pickup : "";
            $dropship_data['enable_local_delivery'] = (isset($value->enable_local_delivery)) ? $value->enable_local_delivery : "";
            $dropship_data['miles_local_delivery'] = (isset($value->miles_local_delivery)) ? $value->miles_local_delivery : "";
            $dropship_data['match_postal_local_delivery'] = (isset($value->match_postal_local_delivery)) ? $value->match_postal_local_delivery : "";
            $dropship_data['checkout_desc_local_delivery'] = (isset($value->checkout_desc_local_delivery)) ? $value->checkout_desc_local_delivery : "";

            $dropship_data['sender_origin'] = $dropship_data['location'] . ": " . $dropship_data['city'] . ", " . $dropship_data['state'] . " " . $dropship_data['zip'];

            return $dropship_data;
        }

        /**
         * get freight class
         * @param $_product
         * @param $variation_id
         * @param $product_id
         * @return string
         */
        function get_freight_class($_product, $variation_id, $product_id)
        {
            if ($_product->get_type() == 'variation') {
                $variation_class = get_post_meta($variation_id, '_ltl_freight_variation', true);

                if ($variation_class == 'get_parent' || $variation_class == 0) {
                    $variation_class = get_post_meta($product_id, '_ltl_freight', true);
                    $freight_class_ltl_gross = $variation_class;
                } else {
                    if ($variation_class > 0) {
                        $freight_class_ltl_gross = get_post_meta($variation_id, '_ltl_freight_variation', true);
                    } else {
                        $freight_class_ltl_gross = get_post_meta($_product->get_id(), '_ltl_freight', true);
                    }
                }
            } else {
                $freight_class_ltl_gross = get_post_meta($_product->get_id(), '_ltl_freight', true);
            }
            return $freight_class_ltl_gross;
        }

        /**
         * Getting Cerasis Enable/Disable
         * @param $_product
         * @return string
         */
        function get_cerasis_enable($_product)
        {
            if ($_product->get_type() == 'variation') {
                $ship_class_id = $_product->get_shipping_class_id();
                if ($ship_class_id == 0) {
                    $parent_data = $_product->get_parent_data();
                    $get_parent_term = get_term_by('id', $parent_data['shipping_class_id'], 'product_shipping_class');
                    $get_shipping_result = (isset($get_parent_term->slug)) ? $get_parent_term->slug : '';
                } else {
                    $get_shipping_result = $_product->get_shipping_class();
                }

                $freight_enable = ($get_shipping_result && $get_shipping_result == 'ltl_freight') ? true : false;
            } else {
                $get_shipping_result = $_product->get_shipping_class();
                $freight_enable = ($get_shipping_result == 'ltl_freight') ? true : false;
            }
            return $freight_enable;
        }
        /**
         * Get the product nmfc number
         */
        public function en_group_package($item, $product_object, $product_detail)
        {
            $en_nmfc_number = $this->en_nmfc_number($product_object, $product_detail);
            $item['nmfc_number'] = $en_nmfc_number;
            return $item;
        }

        /**
         * Get product shippable unit enabled
         */
        public function en_nmfc_number($product_object, $product_detail)
        {
            $post_id = (isset($product_object['variation_id']) && $product_object['variation_id'] > 0) ? $product_object['variation_id'] : $product_detail->get_id();
            return get_post_meta($post_id, '_nmfc_number', true);
        }

        /**
         * Returns flat rate price and quantity
         */
        function en_get_flat_rate_price($values, $_product)
        {
            if ($_product->get_type() == 'variation') {
                $flat_rate_price = get_post_meta($values['variation_id'], 'en_flat_rate_price', true);
                if (strlen($flat_rate_price) < 1) {
                    $flat_rate_price = get_post_meta($values['product_id'], 'en_flat_rate_price', true);
                }
            } else {
                $flat_rate_price = get_post_meta($_product->get_id(), 'en_flat_rate_price', true);
            }

            return $flat_rate_price;
        }

        /**
        * Returns product level markup
        */
        function gtz_ltl_get_product_level_markup($_product, $variation_id, $product_id, $quantity)
        {
            $product_level_markup = 0;
            if ($_product->get_type() == 'variation') {
                $product_level_markup = get_post_meta($variation_id, '_en_product_markup_variation', true);
                if(empty($product_level_markup) || $product_level_markup == 'get_parent'){
                    $product_level_markup = get_post_meta($_product->get_id(), '_en_product_markup', true);
                }
            } else {
                $product_level_markup = get_post_meta($_product->get_id(), '_en_product_markup', true);
            }

            if(empty($product_level_markup)) {
                $product_level_markup = get_post_meta($product_id, '_en_product_markup', true);
            }

            if(!empty($product_level_markup) && strpos($product_level_markup, '%') === false 
            && is_numeric($product_level_markup) && is_numeric($quantity))
            {
                $product_level_markup *= $quantity;
            } else if(!empty($product_level_markup) && strpos($product_level_markup, '%') > 0 && is_numeric($quantity)){
                $position = strpos($product_level_markup, '%');
                $first_str = substr($product_level_markup, $position);
                $arr = explode($first_str, $product_level_markup);
                $percentage_value = $arr[0];
                $product_price = $_product->get_price();
    
                if (!empty($product_price)) {
                    $product_level_markup = $percentage_value / 100 * ($product_price * $quantity);
                } else {
                    $product_level_markup = 0;
                }
            }
    
            return $product_level_markup;
        }

    }

    new Engtz_Cart_To_Request();
}
