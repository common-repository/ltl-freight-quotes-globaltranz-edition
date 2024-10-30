<?php

/**
 * Includes Shipping Rules Ajax Request class
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("EnGtzLtlShippingRulesAjaxReq")) {

    class EnGtzLtlShippingRulesAjaxReq
    {
        /**
         * Get shipping rules ajax request
         */
        public function __construct()
        {
            add_action('wp_ajax_nopriv_en_gtz_ltl_save_shipping_rule', array($this, 'en_gtz_ltl_save_shipping_rule_ajax'));
            add_action('wp_ajax_en_gtz_ltl_save_shipping_rule', array($this, 'en_gtz_ltl_save_shipping_rule_ajax'));

            add_action('wp_ajax_nopriv_en_gtz_ltl_edit_shipping_rule', array($this, 'en_gtz_ltl_edit_shipping_rule_ajax'));
            add_action('wp_ajax_en_gtz_ltl_edit_shipping_rule', array($this, 'en_gtz_ltl_edit_shipping_rule_ajax'));

            add_action('wp_ajax_nopriv_en_gtz_ltl_delete_shipping_rule', array($this, 'en_gtz_ltl_delete_shipping_rule_ajax'));
            add_action('wp_ajax_en_gtz_ltl_delete_shipping_rule', array($this, 'en_gtz_ltl_delete_shipping_rule_ajax'));

            add_action('wp_ajax_nopriv_en_gtz_ltl_update_shipping_rule_status', array($this, 'en_gtz_ltl_update_shipping_rule_status_ajax'));
            add_action('wp_ajax_en_gtz_ltl_update_shipping_rule_status', array($this, 'en_gtz_ltl_update_shipping_rule_status_ajax'));
        }

        // MARK: Save Shipping Rule
        /**
         * Save Shipping Rule Function
         * @global $wpdb
         */
        function en_gtz_ltl_save_shipping_rule_ajax()
        {
            global $wpdb;

            $insert_qry = $update_qry = '';
            $error = false;
            $data = $_POST;
            $get_shipping_rule_id = (isset($data['rule_id']) && intval($data['rule_id'])) ? $data['rule_id'] : "";
            $last_id = $get_shipping_rule_id;
            $qry = "SELECT * FROM " . $wpdb->prefix . "eniture_gtz_ltl_shipping_rules WHERE name = '" . $data['name'] . "'"; 
            $get_shipping_rule = $wpdb->get_results($qry);
            unset($data['action']);
            unset($data['rule_id']);
            
            if (!empty($get_shipping_rule_id)) {
                $data['settings'] = json_encode($data['settings']);
                $update_qry = $wpdb->update(
                    $wpdb->prefix . 'eniture_gtz_ltl_shipping_rules', $data, array('id' => $get_shipping_rule_id)
                );

                $update_qry = (!empty($get_shipping_rule) && reset($get_shipping_rule)->id == $get_shipping_rule_id) ? 1 : $update_qry;
            } else {
                if (!empty($get_shipping_rule)) {
                    $error = true;
                } else {
                    $data['settings'] = json_encode($data['settings']);
                    $insert_qry = $wpdb->insert($wpdb->prefix . 'eniture_gtz_ltl_shipping_rules', $data);
                    $last_id = $wpdb->insert_id;
                }
            }

            $shipping_rules_list = array('name' => $data["name"], 'type' => $data["type"], 'is_active' => $data["is_active"], 'insert_qry' => $insert_qry, 'update_qry' => $update_qry, 'id' => $last_id, 'error' => $error);

            echo json_encode($shipping_rules_list);
            exit;
        }

        // MARK: Edit Shipping Rule
        /**
         * Edit Shipping Rule Function
         * @global $wpdb
         */
        function en_gtz_ltl_edit_shipping_rule_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['edit_id']) && intval($_POST['edit_id'])) ? $_POST['edit_id'] : "";
            $shipping_rules_list = $wpdb->get_results(
                "SELECT * FROM " . $wpdb->prefix . "eniture_gtz_ltl_shipping_rules WHERE id=$get_shipping_rule_id"
            );
            $product_tags_markup = $this->en_gtz_ltl_get_product_tags_markup($shipping_rules_list);
            $data = ['rule_data' => reset($shipping_rules_list), 'product_tags_markup' => $product_tags_markup];

            echo json_encode($data);
            exit;
        }

        // MARK: Delete Shipping Rule
        /**
         * Delete Shipping Rule Function
         * @global $wpdb
         */
        function en_gtz_ltl_delete_shipping_rule_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['delete_id']) && intval($_POST['delete_id'])) ? $_POST['delete_id'] : "";
            $qry = $wpdb->delete($wpdb->prefix . 'eniture_gtz_ltl_shipping_rules', array('id' => $get_shipping_rule_id));

            echo json_encode(['query' => $qry]);
            exit;
        }

        // MARK: Update Shipping Rule Status
        /**
         * Update Shipping Rule Status Function
         * @global $wpdb
         */
        function en_gtz_ltl_update_shipping_rule_status_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['rule_id']) && intval($_POST['rule_id'])) ? $_POST['rule_id'] : "";
            $is_active = isset($_POST['is_active']) ? $_POST['is_active'] : "";
            $data = ['is_active' => $is_active];
            
            $update_qry = $wpdb->update(
                $wpdb->prefix . 'eniture_gtz_ltl_shipping_rules', $data, array('id' => $get_shipping_rule_id)
            );

            echo json_encode(['id' => $get_shipping_rule_id, 'is_active' => $is_active, 'update_qry' => $update_qry]);
            exit;
        }

        // MARK: Get Product Tags
        /**
         * Get Product Tags Function
         * @global $wpdb
         */
        function en_gtz_ltl_get_product_tags_markup($shipping_rules_list)
        {
            $tags_options = '';
            $shipping_rules_list = reset($shipping_rules_list);
            $tags_data = isset($shipping_rules_list->settings) ? json_decode($shipping_rules_list->settings, true) : [];
            $selected_tags_detials = $this->en_gtz_ltl_get_selected_tags_details($tags_data['filter_by_product_tag_value']);

            if (!empty($selected_tags_detials) && is_array($selected_tags_detials)) {
                foreach ($selected_tags_detials as $key => $tag) {
                    $tags_options .= "<option selected='selected' value='" . esc_attr($tag['term_taxonomy_id']) . "'>" . esc_html($tag['name']) . "</option>";
                }
            }

            if (empty($tags_data['filter_by_product_tag_value']) || !is_array($tags_data['filter_by_product_tag_value'])) {
                $tags_data['filter_by_product_tag_value'] = [];
            }

            $en_woo_product_tags = get_tags( array( 'taxonomy' => 'product_tag' ) );
            if (!empty($en_woo_product_tags) && is_array($tags_data['filter_by_product_tag_value'])) {
                foreach ($en_woo_product_tags as $key => $tag) {
                    if (!in_array($tag->term_id, $tags_data['filter_by_product_tag_value'])) {
                        $tags_options .= "<option value='" . esc_attr($tag->term_taxonomy_id) . "'>" . esc_html($tag->name) . "</option>";
                    }
                }
            }

            return $tags_options;
        }

        // MARK: Get Selected Tags Details
        /**
         * Get Selected Tags Details Function
         * @global $wpdb
         */
        function en_gtz_ltl_get_selected_tags_details($products_tags_arr)
        {
            if (empty($products_tags_arr) || !is_array($products_tags_arr)) {
                return [];
            }

            $tags_detail = [];
            $count = 0;
            $en_woo_product_tags = get_tags( array( 'taxonomy' => 'product_tag' ) );

            if (isset($en_woo_product_tags) && !empty($en_woo_product_tags)) {
                foreach ($en_woo_product_tags as $key => $tag) {
                    if (in_array($tag->term_taxonomy_id, $products_tags_arr)) {
                        $tags_detail[$count]['term_id'] = $tag->term_id;
                        $tags_detail[$count]['name'] = $tag->name;
                        $tags_detail[$count]['slug'] = $tag->slug;
                        $tags_detail[$count]['term_taxonomy_id'] = $tag->term_taxonomy_id;
                        $tags_detail[$count]['description'] = $tag->description;
                        $count++;
                    }
                }
            }

            return $tags_detail;
        }

	    // MARK: Apply Shipping Rules
        /**
         * Apply shipping rules based on Gtz package and settings.
         *
         * @param array $gtz_ltl_package request to get quotes
         * @param boolean $apply_on_rates whether to apply rules on rates
         * @param object $rates quotes response
         * @return boolean | object returns if rule is applied or modified rates
         */
        function apply_shipping_rules($gtz_ltl_package, $apply_on_rates = false, $rates = [], $loc_id = '')
        {
            if (empty($gtz_ltl_package)) return $apply_on_rates ? $rates : false;
            if ($apply_on_rates && empty($rates)) return $rates;

            global $wpdb;
            $qry = "SELECT * FROM " . $wpdb->prefix . "eniture_gtz_ltl_shipping_rules"; 
            $rules = $wpdb->get_results($qry, ARRAY_A);

            if (empty($rules)) return $apply_on_rates ? $rates : false;
        
            $is_rule_applied = false;
            foreach ($rules as $rule) {
                if (!$rule['is_active']) continue;

                $settings = isset($rule['settings']) ? json_decode($rule['settings'], true) : [];
                if (empty($settings)) continue;

                $rule_type = isset($rule['type']) ? $rule['type'] : '';

                if ($rule_type == 'Hide Methods' && !$apply_on_rates) {
                    $is_rule_applied = $this->apply_hide_methods_rule($settings, $gtz_ltl_package);
                    if ($is_rule_applied) break;
                } else if ($rule_type == 'Override Rates' && $apply_on_rates) {
                    $rates = $this->apply_override_rates_rule($gtz_ltl_package, $settings, $rates, $loc_id);
                } 
            }

            return $apply_on_rates ? $rates : $is_rule_applied;
        }

        /**
         * Apply the rule to the given settings and package.
         *
         * @param array $settings The settings for the rule.
         * @param array $gtz_ltl_package The package to apply the rule to.
         * @return bool Whether the rule was applied or not.
        */
        function apply_hide_methods_rule($settings, $gtz_ltl_package)
        {
            $is_rule_applied = false;

            if ($settings['apply_to'] == 'cart') {
                $formatted_values = $this->get_formatted_values($gtz_ltl_package);
                $is_rule_applied = $this->apply_rule_filters($settings, $formatted_values);
            } else {
                if (!empty($gtz_ltl_package['origin'])) {
                    foreach ($gtz_ltl_package['origin'] as $pkg) {
                        if (empty($pkg['zip'])) continue;

                        $is_rule_applied = false;
                        $formatted_values = $this->get_formatted_values($gtz_ltl_package, $pkg['zip']);
                        $is_rule_applied = $this->apply_rule_filters($settings, $formatted_values);
                        if ($is_rule_applied) break;
                    }
                }
            }

            return $is_rule_applied;
        }

        /**
         * A function to apply override rates rule.
         *
         * @param array $gtz_ltl_package request array to get the quotes
         * @param array $settings rule settings
         * @param object $rates quotes object
         * @return $rates The updated rates.
         */
        function apply_override_rates_rule($gtz_ltl_package, $settings, $rates, $loc_id)
        {
            if (empty($rates) || empty($gtz_ltl_package['origin'])) return $rates;
            $updated_rates = $rates;

            $shipments = array_unique(array_column($gtz_ltl_package['origin'], 'zip'));
            foreach ($shipments as $value) {
                if (empty($value) || $value != $loc_id) continue;
                
                $is_rule_applied = false;
                $formatted_values = $this->get_formatted_values($gtz_ltl_package, $value);
                $is_rule_applied = $this->apply_rule_filters($settings, $formatted_values);
                $is_rule_applied && $updated_rates = $this->get_updated_rates($updated_rates, $settings);
            }

            return $updated_rates;
        }

        /**
         * A function that updates rates based on settings and rule type.
         *
         * @param object $rates The rates to be updated.
         * @param array $settings The settings used for updating rates.
         * @return array The updated rates.
         */
        function get_updated_rates($rates, $settings)
        {
            if (empty($rates)) return $rates;

            $service_type = $settings['service'];
            $service_rate = $settings['service_rate'];
            $residential_status = isset($rates->residentialStatus) && $rates->residentialStatus == 'r';
            $liftgate_status = isset($rates->liftGateStatus) && $rates->liftGateStatus == 'l';
            $gtz_or_new_api_enabled = get_option('cerasis_global_tranz_api_endpoint') != 'wc_cerasis_api_fields';
            $additional_services = ['residential_delivery_service' => 'Residential Delivery', 'liftgate_delivery_service' => 'Liftgate Delivery', 'notify_before_delivery_service' => 'Notify prior to arrival'];
            
            if ($service_type == 'transportation_service') {
                $rates = $this->get_transportation_service_rates($rates, $service_rate);
            } elseif (in_array($service_type, array_keys($additional_services)) && $gtz_or_new_api_enabled) {
                $rates = $this->get_additional_services_rates($rates, $service_rate, $additional_services[$service_type]);
            }

            return $rates;
        }

        /**
         * Calculate the total weight, price, quantity, and tags for a list of shipments.
         *
         * @param array $shipments An array of shipments to process.
         * @param int $org_id The origin id.
         * @return array The formatted values including weight, price, quantity, tags, and country.
         */
        function get_formatted_values($shipments, $org_id = null)
        {
            $formatted_values = ['weight' => 0, 'price' => 0, 'quantity' => 0, 'tags' => []];
            if (empty($shipments) || empty($shipments['origin'])) return $formatted_values;

            $org_zips = array_unique(array_column($shipments['origin'], 'zip'));
            foreach ($org_zips as $zip) {
                if (empty($zip) || (!empty($org_id) && $zip != $org_id)) continue;

                $formatted_values['weight'] += isset($shipments['shipment_weight'][$zip]['cerasis']) ? floatval($shipments['shipment_weight'][$zip]['cerasis']) : 0;
                $formatted_values['price'] += isset($shipments['product_prices'][$zip]['cerasis']) ? floatval($shipments['product_prices'][$zip]['cerasis']) : 0;
                $formatted_values['quantity'] += isset($shipments['product_quantities'][$zip]['cerasis']) ? floatval($shipments['product_quantities'][$zip]['cerasis']) : 0;
                $formatted_values['tags'] = !empty($shipments['product_tags'][$zip]['cerasis']) ? array_merge($formatted_values['tags'], $shipments['product_tags'][$zip]['cerasis']) : [];
            }

            return $formatted_values;
        }

        /**
         * Apply rule filters to determine if the rule is applied.
         *
         * @param array $settings The settings for the rule filters
         * @param array $formatted_values The formatted values for comparison
         * @return bool Whether the rule filters are applied
         */
        function apply_rule_filters($settings, $formatted_values)
        {
            // If there is no filter check, then all rules will meet so rule will be treated as applied
            if (!$this->is_any_filter_checked($settings)) return true;

            $is_filter_applied = false;
            $filters = ['weight', 'price', 'quantity'];

            foreach ($filters as $filter) {
                if (filter_var($settings['filter_by_' . $filter], FILTER_VALIDATE_BOOLEAN)) {
                    $is_filter_applied = $formatted_values[$filter] >= $settings['filter_by_' . $filter . '_from'];
                    if ($is_filter_applied && !empty($settings['filter_by_' . $filter . '_to'])) {
                        $is_filter_applied = $formatted_values[$filter] < $settings['filter_by_' . $filter . '_to'];
                    }
                }

                if ($is_filter_applied) break;
            }

            if (!$is_filter_applied && filter_var($settings['filter_by_product_tag'], FILTER_VALIDATE_BOOLEAN)) {
                $product_tags = $settings['filter_by_product_tag_value'];
                $tags_check = array_filter($product_tags, function ($tag) use ($formatted_values) {
                    return in_array($tag, $formatted_values['tags']);
                });
                $is_filter_applied = count($tags_check) > 0;
            }

            return $is_filter_applied;
        }

        /**
         * A function that checks if any filter is checked based on the provided settings.
         *
         * @param array $settings The settings containing filter values.
         * @return bool Returns true if any filter is checked, false otherwise.
         */
        function is_any_filter_checked($settings)
        {
            $filters_checks = ['weight', 'price', 'quantity', 'product_tag'];
            
            // Check if any of the filter is checked
            $any_filter_checked = false;
            foreach ($filters_checks as $check) {
                if (isset($settings['filter_by_' . $check]) && filter_var($settings['filter_by_' . $check], FILTER_VALIDATE_BOOLEAN)) {
                    $any_filter_checked = true;
                    break;
                }
            }

            return $any_filter_checked;
        }
        
        /**
         * A function that updates service rates based on certain conditions.
         *
         * @param object $rates The object containing normal and direct service rates.
         * @param float $service_rate The service rate to be applied.
         * @return object The updated rates object after applying the service rate.
         */
        function get_transportation_service_rates($rates, $service_rate)
        {
            $rates_keys = ['q', 'quotesWithoutAccessorial'];
            $new_api_enabled = get_option('cerasis_global_tranz_api_endpoint') == 'wc_global_tranz_new_api_fields';

            foreach ($rates_keys as $key) {
                if (!isset($rates[$key]) || empty($rates[$key])) continue;
                $quote_results = $rates[$key];

                // loop through all the services and update the rate with the service rate
                foreach ($quote_results as $quote_key => $quote) {
                    if (empty($quote)) continue;
                    // format surcharges of new API to match legacy API format
                    if ($new_api_enabled) $quote = $this->get_new_api_formatted_surcharges($quote);

                    // get surcharges
                    $surcharges = isset($quote['Charges']) ? $quote['Charges'] : [];
                    // update the rate in the legacy API
                    isset($quote['LtlAmount']) && $quote['LtlAmount'] = floatval($service_rate) + $this->get_additional_services_fees($surcharges);
                    // update the rate in the new API
                    isset($quote['totalNetCharge']['Amount']) && $quote['totalNetCharge']['Amount'] = floatval($service_rate) + $this->get_additional_services_fees($surcharges);
                    // update the rate in the cerasis API
                    isset($quote['ShipmentRate']) && $quote['ShipmentRate'] = floatval($service_rate) + $this->get_additional_services_fees($surcharges);

                    $quote_results[$quote_key] = $quote;
                }

                $rates[$key] = $quote_results;
            }

            return $rates;
        }

        /**
         * Updates the additional services rates in the given $rates object based on the $type and $service_rate.
         *
         * @param object $rates The object containing the service rates
         * @param string $service_rate The new service rate to be applied
         * @param string $type The type of service rate to be updated
         * @return object The updated $rates object
         */
        function get_additional_services_rates($rates, $service_rate, $type)
        {
            $rates_keys = ['q', 'quotesWithoutAccessorial'];
            $new_api_enabled = get_option('cerasis_global_tranz_api_endpoint') == 'wc_global_tranz_new_api_fields';

            foreach ($rates_keys as $key) {
                if (!isset($rates[$key]) || empty($rates[$key])) continue;

                $quote_results = $rates[$key];
                foreach ($quote_results as $quote_key => $quote) {
                    if (empty($quote)) continue;
                    if ($new_api_enabled) $quote = $this->get_new_api_formatted_surcharges($quote);

                    $surcharges = isset($quote['Charges']) ? $quote['Charges'] : [];
                    if (empty($surcharges)) continue;

                    $surcharges_fee = 0;
                    $is_surcharge_exist = false;

                    foreach ($surcharges as $surcharge_key => $surcharge) {
                        if (!isset($surcharge['Name']) || $surcharge['Name'] != $type || !isset($surcharge['Charge']) || empty($surcharge['Charge'])) continue;

                        $surcharges_fee = $surcharge['Charge'];
                        $quote['Charges'][$surcharge_key]['Charge'] = $service_rate;

                        // Update the surcharges in case of new API
                        if ($new_api_enabled && !empty($quote['surcharges'])) {
                            foreach ($quote['surcharges'] as $s_key => $value) {
                                $s_key == 'residentialFee' && $type == 'Residential Delivery' && $quote['surcharges'][$s_key] = $service_rate;
                                $s_key == 'liftgateFee' && $type == 'Liftgate Delivery' && $quote['surcharges'][$s_key] = $service_rate;
                                $s_key == 'notifyDeliveryFee' && $type == 'Notify prior to arrival' && $quote['surcharges'][$s_key] = $service_rate;
                            }
                        }

                        $is_surcharge_exist = true;
                        break;
                    }

                    if (!$is_surcharge_exist) continue;

                    $rate_charge = isset($quote['LtlAmount']) ? $quote['LtlAmount'] : 0;
                    $rate_charge = isset($quote['totalNetCharge']['Amount']) ? $quote['totalNetCharge']['Amount'] : $rate_charge;
                    $rate_charge = floatval($rate_charge) - floatval($surcharges_fee);
                    $rate_charge += floatval($service_rate);
                    isset($quote['LtlAmount']) && $quote['LtlAmount'] = $rate_charge;
                    isset($quote['totalNetCharge']['Amount']) && $quote['totalNetCharge']['Amount'] = $rate_charge;

                    $quote_results[$quote_key] = $quote;
                }

                $rates[$key] = $quote_results;
            }

            return $rates;
        }

        /**
         * Get the total fees for residential, liftgate and notify delivery surcharges.
         *
         * @param object $surcharges An array of surcharges objects.
         * @return float The total surcharges fee.
         */
        function get_additional_services_fees($surcharges)
        {
            if (empty($surcharges)) return 0;
            
            $surcharges_fee = 0;
            $add_srvcs = ['Residential Delivery', 'Liftgate Delivery', 'Notify prior to arrival'];
            foreach ($surcharges as $surcharge) {
                if (isset($surcharge['Name']) && (in_array($surcharge['Name'], $add_srvcs))) {
                    $surcharges_fee += floatval($surcharge['Charge']);
                }
            }

            return $surcharges_fee;
        }

        function get_new_api_formatted_surcharges($quote)
        {
            if (!empty($quote['surcharges'])) {
                $surcharges = [];
                if (isset($quote['surcharges']['liftgateFee'])) {
                    $surcharges[] = ['Name' => 'Liftgate Delivery', 'Charge' => $quote['surcharges']['liftgateFee']];
                }

                if (isset($quote['surcharges']['residentialFee'])) {
                    $surcharges[] = ['Name' => 'Residential Delivery', 'Charge' => $quote['surcharges']['residentialFee']];
                }

                if (isset($quote['surcharges']['notifyDeliveryFee'])) {
                    $surcharges[] = ['Name' => 'Notify prior to arrival', 'Charge' => $quote['surcharges']['notifyDeliveryFee']];
                }
            
                $quote['Charges'] = $surcharges;
            }

            return $quote;
        }

        /**
         * Get the liftgate exclude limit.
         * @return int Returns the liftgate exclude limit or 0 if no limit found.
         */
        function get_liftgate_exclude_limit()
        {
            global $wpdb;
            $qry = "SELECT * FROM " . $wpdb->prefix . "eniture_gtz_ltl_shipping_rules"; 
            $rules = $wpdb->get_results($qry, ARRAY_A);

            if (empty($rules)) return 0;

            $liftgate_exclude_limit = 0;
            foreach ($rules as $rule) {
                if (!$rule['is_active']) continue;
                
                $settings = isset($rule['settings']) ? json_decode($rule['settings'], true) : [];
                if (empty($settings)) continue;

                $rule_type = isset($rule['type']) ? $rule['type'] : '';
                if ($rule_type == 'Liftgate Weight Restrictions' && !empty($settings['liftgate_weight_restrictions'])) {
                    $liftgate_exclude_limit = $settings['liftgate_weight_restrictions'];
                    break;
                }
            }

            return $liftgate_exclude_limit;
        }
    }
}

new EnGtzLtlShippingRulesAjaxReq();
