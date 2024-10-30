<?php

/**
 * Cerasis Quote Settings Tab Class
 * @package     Woocommerce GlobalTranz Edition
 * @author      <https://eniture.com/>
 * @version     v.1..0 (01/10/2017)
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cerasis Quote Settings Form Class
 */
if (!class_exists('Engtz_Cerasis_Quote_Settings')) {

    class Engtz_Cerasis_Quote_Settings
    {
        /**
         * quote setting form
         * @return array
         */
        function quote_settings_tab()
        {
            // Cuttoff Time
            $gt_disable_cutt_off_time_ship_date_offset = "";
            $gt_cutt_off_time_package_required = "";

            //  Check the cutt of time & offset days plans for disable input fields
            $gt_action_cutOffTime_shipDateOffset = apply_filters('globaltranz_quotes_plans_suscription_and_features', 'gt_cutt_off_time');
            if (is_array($gt_action_cutOffTime_shipDateOffset)) {
                $gt_disable_cutt_off_time_ship_date_offset = "disabled_me";
                $gt_cutt_off_time_package_required = apply_filters('globaltranz_plans_notification_link', $gt_action_cutOffTime_shipDateOffset);
            }

            if (empty(get_option('gtz_ltl_backup_rates_display'))) {
                update_option('gtz_ltl_backup_rates_display', 'no_other_rates');
            }
            
            $disable_rating_method = "";
            $disable_lowest_quickest = "";
            $gt_rating_status = get_option('service_global_tranz_rating_method_ch_unch');
            if($gt_rating_status != 'yes') {
                $disable_rating_method = 'disabled_me';
            }else {
                $disable_lowest_quickest = 'disabled_me';
            }
            $dynamic_settings = [];
            $ltl_enable = get_option('en_plugins_return_LTL_quotes');
            $weight_threshold_class = $ltl_enable == 'yes' ? 'show_en_weight_threshold_lfq' : 'hide_en_weight_threshold_lfq';
            $weight_threshold = get_option('en_weight_threshold_lfq');
            $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;

            $cerasis_global_tranz_api_endpoint = get_option('cerasis_global_tranz_api_endpoint');
            if ($cerasis_global_tranz_api_endpoint == 'wc_global_tranz_api_fields' || $cerasis_global_tranz_api_endpoint == 'wc_global_tranz_new_api_fields') {
                $dynamic_settings = [
                    'section_title_quote' => array(
                        'title' => __('', 'wc-settings-cerasis_quotes'),
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'wc_settings_cerasis_title_quote'
                    ),
                    //for new carriers
                    'service_global_tranz_rating_method_ch_unch' => array(
                        'name' => __('Enable Rating Method', 'woocommerce-settings-globaltranz'),
                        'type' => 'checkbox',
                        'id' => 'service_global_tranz_rating_method_ch_unch',
                        'class' => 'global_tranz_rating_methods',
                    ),
                    'rating_method_cerasis' => array(
                        'name' => __('Rating Method ', 'wc-settings-cerasis_quotes'),
                        'type' => 'select',
                        'desc' => __('Displays only the cheapest returned Rate.', 'wc-settings-cerasis_quotes'),
                        'class' => $disable_rating_method,
                        'id' => 'wc_settings_cerasis_rate_method',
                        'options' => array(
                            'Cheapest' => __('Cheapest', 'Cheapest'),
                            'cheapest_options' => __('Cheapest Options', 'cheapest_options'),
                            'average_rate' => __('Average Rate', 'average_rate')
                        )
                    ),
                    'number_of_options_cerasis' => array(
                        'name' => __('Number Of Options ', 'wc-settings-cerasis_quotes'),
                        'type' => 'select',
                        'default' => '3',
                        'desc' => __('Number of options to display in the shopping cart.', 'wc-settings-cerasis_quotes'),
                        'class' => $disable_rating_method,
                        'id' => 'wc_settings_cerasis_Number_of_options',
                        'options' => array(
                            '1' => __('1', '1'),
                            '2' => __('2', '2'),
                            '3' => __('3', '3'),
                            '4' => __('4', '4'),
                            '5' => __('5', '5'),
                            '6' => __('6', '6'),
                            '7' => __('7', '7'),
                            '8' => __('8', '8'),
                            '9' => __('9', '9'),
                            '10' => __('10', '10')
                        )
                    ),
                    'label_as_cerasis' => array(
                        'name' => __('Label As ', 'wc-settings-cerasis_quotes'),
                        'type' => 'text',
                        'desc' => __('What The User Sees During Checkout, e.g "Freight" Leave Blank to Display The Carrier Name.', 'wc-settings-cerasis_quotes'),
                        'class' => $disable_rating_method,
                        'id' => 'wc_settings_cerasis_label_as'
                    ),
                    //quote service label
                    'sevice_global_tranz_label_as_title' => array(
                        'name' => __('Quote Service Label As ', 'woocommerce-settings-globaltranz'),
                        'type' => 'text',
                        'class' => 'hidden',
                        'id' => 'sevice_global_tranz_label_as_title'
                    ),

                    'wc_settings_globaltranz_lowest_label_as' => array(
                        'name' => __('Lowest Cost Rate ', 'wc-settings-cerasis_quotes'),
                        'type' => 'text',
                        'desc' => __('What The User Sees During Checkout, e.g "Freight" Leave Blank to Display Lowest Cost Rate.', 'wc-settings-cerasis_quotes'),
                        'class' => $disable_lowest_quickest,
                        'id' => 'wc_settings_globaltranz_lowest_label_as'
                    ),
                    'wc_settings_globaltranz_quickest_label_as' => array(
                        'name' => __('Quickest Transit Rate ', 'wc-settings-cerasis_quotes'),
                        'type' => 'text',
                        'desc' => __('What The User Sees During Checkout, e.g "Freight" Leave Blank to Display Quickest Transit Rate.', 'wc-settings-cerasis_quotes'),
                        'class' => $disable_lowest_quickest,
                        'id' => 'wc_settings_globaltranz_quickest_label_as'
                    ),
                    //quote service label
                    'sevice_global_tranz_label_as_title' => array(
                        'name' => __('Quote Service Label As ', 'woocommerce-settings-globaltranz'),
                        'type' => 'text',
                        'class' => 'hidden',
                        'id' => 'sevice_global_tranz_label_as_title'
                    ),

                    'wc_settings_globaltranz_lowest_label_as' => array(
                        'name' => __('Lowest Cost Rate ', 'wc-settings-cerasis_quotes'),
                        'type' => 'text',
                        'desc' => __('What The User Sees During Checkout, e.g "Freight" Leave Blank to Display Lowest Cost Rate.', 'wc-settings-cerasis_quotes'),
                        'id' => 'wc_settings_globaltranz_lowest_label_as'
                    ),
                    'wc_settings_globaltranz_quickest_label_as' => array(
                        'name' => __('Quickest Transit Rate ', 'wc-settings-cerasis_quotes'),
                        'type' => 'text',
                        'desc' => __('What The User Sees During Checkout, e.g "Freight" Leave Blank to Display Quickest Transit Rate.', 'wc-settings-cerasis_quotes'),
                        'id' => 'wc_settings_globaltranz_quickest_label_as'
                    ),
                    // Global tranz
                    'sevice_global_tranz_title' => array(
                        'name' => __('Quote Service Options ', 'woocommerce-settings-globaltranz'),
                        'type' => 'text',
                        'class' => 'hidden',
                        'id' => 'sevice_global_tranz_title'
                    ),
                    'cerasis_global_tranz_api_endpoint_quote_settings' => array(
                        'name' => __('', 'wc-settings-cerasis_quotes'),
                        'type' => 'text',
                        'value' => get_option('cerasis_global_tranz_api_endpoint'),
                        'class' => 'hidden cerasis_global_tranz_api_endpoint_class',
                        'id' => 'cerasis_global_tranz_api_endpoint_quote_settings'
                    ),
                    'select_global_tranz_services' => array(
                        'name' => __('Select All', 'woocommerce-settings-globaltranz'),
                        'type' => 'checkbox',
                        'id' => 'select_all_global_tranz_services',
                        'class' => 'global_tranz_all_services',
                    ),
                    'service_global_tranz_lowest_cost_rate' => array(
                        'name' => __('Lowest Cost Rate', 'woocommerce-settings-globaltranz'),
                        'type' => 'checkbox',
                        'id' => 'service_global_tranz_lowest_cost_rate_quotes',
                        'class' => 'global_tranz_quotes_services',
                    ),
                    'service_global_tranz_quickest_transit_rate' => array(
                        'name' => __('Quickest Transit Rate', 'woocommerce-settings-globaltranz'),
                        'type' => 'checkbox',
                        'id' => 'service_global_tranz_quickest_transit_rate_quotes',
                        'class' => 'global_tranz_quotes_services',
                    ),
                ];
            } else {
                $final_mile_status = get_option('cerasis_global_tranz_shipping_service');
                if($final_mile_status != 'wc_final_mile_service') {
                    $dynamic_settings = [
                        'section_title_quote' => array(
                            'title' => __('', 'wc-settings-cerasis_quotes'),
                            'type' => 'title',
                            'desc' => '',
                            'id' => 'wc_settings_cerasis_title_quote'
                        ),
                        'rating_method_cerasis' => array(
                            'name' => __('Rating Method ', 'wc-settings-cerasis_quotes'),
                            'type' => 'select',
                            'desc' => __('Displays only the cheapest returned Rate.', 'wc-settings-cerasis_quotes'),
                            'id' => 'wc_settings_cerasis_rate_method',
                            'options' => array(
                                'Cheapest' => __('Cheapest', 'Cheapest'),
                                'cheapest_options' => __('Cheapest Options', 'cheapest_options'),
                                'average_rate' => __('Average Rate', 'average_rate')
                            )
                        ),
                        'number_of_options_cerasis' => array(
                            'name' => __('Number Of Options ', 'wc-settings-cerasis_quotes'),
                            'type' => 'select',
                            'default' => '3',
                            'desc' => __('Number of options to display in the shopping cart.', 'wc-settings-cerasis_quotes'),
                            'id' => 'wc_settings_cerasis_Number_of_options',
                            'options' => array(
                                '1' => __('1', '1'),
                                '2' => __('2', '2'),
                                '3' => __('3', '3'),
                                '4' => __('4', '4'),
                                '5' => __('5', '5'),
                                '6' => __('6', '6'),
                                '7' => __('7', '7'),
                                '8' => __('8', '8'),
                                '9' => __('9', '9'),
                                '10' => __('10', '10')
                            )
                        ),
                        'label_as_cerasis' => array(
                            'name' => __('Label As ', 'wc-settings-cerasis_quotes'),
                            'type' => 'text',
                            'desc' => __('What The User Sees During Checkout, e.g "Freight" Leave Blank to Display The Carrier Name.', 'wc-settings-cerasis_quotes'),
                            'id' => 'wc_settings_cerasis_label_as'
                        ),
                    ];
                }
            }

            // LTL services Final Mile OR LTL Service
            $shipping_services = [
                'section_shipping_service_quote' => array(
                    'title' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'section_shipping_service_quote'
                ),
                'cerasis_global_tranz_shipping_service' => array(
                    'name' => __('Shipping Service ', 'wc-settings-cerasis_quotes'),
                    'type' => 'select',
                    'default' => 'wc_standard_lfq_service',
                    'id' => 'cerasis_global_tranz_shipping_service',
                    'options' => array(
                        'wc_standard_lfq_service' => __('Standard LTL Freight Services', 'Standard LTL Freight Services'),
                        'wc_final_mile_service' => __('Final Mile Services', 'Final Mile Services'),
                    )
                ),
                'section_cerasis_shipping_services_end_quote' => array(
                    'type' => 'sectionend',
                    'id' => 'section_cerasis_shipping_services_end_quote'
                )
            ];

            // Final Mile
            $final_final_mile = [
                'section_final_mile_quote' => array(
                    'title' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'section_final_mile_quote'
                ),
                'en_cerasis_final_mile_label' => array(
                    'name' => __('Final Mile Services', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'id' => 'en_cerasis_final_mile_label'
                ),
                'en_cerasis_threshold_checkbox' => array(
                    'name' => __('Threshold', 'wc-settings-cerasis_quotes'),
                    'type' => 'checkbox',
                    'desc' => '',
                    'class' => 'wc_final_mile_service checkbox_fr_add_fm',
                    'id' => 'en_cerasis_threshold_checkbox'
                ),
                'en_cerasis_threshold_label' => array(
                    'name' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'placeholder' => '',
                    'desc' => 'Label As',
                    'class' => 'wc_final_mile_service',
                    'id' => 'en_cerasis_threshold_label',
                ),
                'en_cerasis_room_of_choice_checkbox' => array(
                    'name' => __('Room of Choice', 'wc-settings-cerasis_quotes'),
                    'type' => 'checkbox',
                    'desc' => '',
                    'class' => 'wc_final_mile_service checkbox_fr_add_fm',
                    'id' => 'en_cerasis_room_of_choice_checkbox'
                ),
                'en_cerasis_room_of_choice_label' => array(
                    'name' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'placeholder' => '',
                    'desc' => 'Label As',
                    'class' => 'wc_final_mile_service',
                    'id' => 'en_cerasis_room_of_choice_label',
                ),
                'en_cerasis_premium_checkbox' => array(
                    'name' => __('Premium', 'wc-settings-cerasis_quotes'),
                    'type' => 'checkbox',
                    'desc' => '',
                    'class' => 'wc_final_mile_service checkbox_fr_add_fm',
                    'id' => 'en_cerasis_premium_checkbox'
                ),
                'en_cerasis_premium_label' => array(
                    'name' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'placeholder' => '',
                    'desc' => 'Label As',
                    'class' => 'wc_final_mile_service',
                    'id' => 'en_cerasis_premium_label',
                ),
                'section_end_final_mile_quote' => array(
                    'type' => 'sectionend',
                    'id' => 'section_end_final_mile_quote'
                ),
            ];

            $cerasis_temp = $cerasis_global_tranz_api_endpoint == 'wc_cerasis_api_fields' ? $shipping_services + $final_final_mile + $dynamic_settings : $dynamic_settings;

            $start_common_settings = array(
                'section_common_title_quote' => array(
                    'title' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'section_common_title_quote'
                ),
                //** Start Delivery Estimate Options - Cuttoff Time
                'service_gt_estimates_title' => array(
                    'name' => __('Delivery Estimate Options ', 'woocommerce-settings-en_woo_addons_packages_quotes'),
                    'type' => 'text',
                    'desc' => '',
                    'id' => 'service_gt_estimates_title'
                ),
                'gt_show_delivery_estimates_options_radio' => array(
                    'name' => __("", 'woocommerce-settings-gt'),
                    'type' => 'radio',
                    'default' => 'dont_show_estimates',
                    'options' => array(
                        'dont_show_estimates' => __("Don't display delivery estimates.", 'woocommerce'),
                        'delivery_days' => __("Display estimated number of days until delivery.", 'woocommerce'),
                        'delivery_date' => __("Display estimated delivery date.", 'woocommerce'),
                    ),
                    'id' => 'gt_delivery_estimates',
                    'class' => 'gt_dont_show_estimate_option',
                ),
                //** End Delivery Estimate Options
                
                //**Start: Cut Off Time & Ship Date Offset
                'cutOffTime_shipDateOffset_gt_freight' => array(
                    'name' => __('Cut Off Time & Ship Date Offset ', 'woocommerce-settings-en_woo_addons_packages_quotes'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'desc' => $gt_cutt_off_time_package_required,
                    'id' => 'gt_freight_cutt_off_time_ship_date_offset'
                ),
                'orderCutoffTime_gt_freight' => array(
                    'name' => __('Order Cut Off Time ', 'woocommerce-settings-gt_freight_freight_orderCutoffTime'),
                    'type' => 'text',
                    'placeholder' => '-- : -- --',
                    'desc' => 'Enter the cut off time (e.g. 2.00) for the orders. Orders placed after this time will be quoted as shipping the next business day.',
                    'id' => 'gt_freight_order_cut_off_time',
                    'class' => $gt_disable_cutt_off_time_ship_date_offset,
                ),
                'shipmentOffsetDays_gt_freight' => array(
                    'name' => __('Fullfillment Offset Days ', 'woocommerce-settings-gt_freight_shipment_offset_days'),
                    'type' => 'text',
                    'desc' => 'The number of days the ship date needs to be moved to allow the processing of the order.',
                    'placeholder' => 'Fullfillment Offset Days, e.g. 2',
                    'id' => 'gt_freight_shipment_offset_days',
                    'class' => $gt_disable_cutt_off_time_ship_date_offset,
                ),
                'all_shipment_days_gt' => array(
                    'name' => __("What days do you ship orders?", 'woocommerce-settings-gt_quotes'),
                    'type' => 'checkbox',
                    'desc' => 'Select All',
                    'class' => "all_shipment_days_gt $gt_disable_cutt_off_time_ship_date_offset",
                    'id' => 'all_shipment_days_gt'
                ),
                'monday_shipment_day_gt' => array(
                    'name' => __("", 'woocommerce-settings-gt_quotes'),
                    'type' => 'checkbox',
                    'desc' => 'Monday',
                    'class' => "gt_shipment_day $gt_disable_cutt_off_time_ship_date_offset",
                    'id' => 'monday_shipment_day_gt'
                ),
                'tuesday_shipment_day_gt' => array(
                    'name' => __("", 'woocommerce-settings-gt_quotes'),
                    'type' => 'checkbox',
                    'desc' => 'Tuesday',
                    'class' => "gt_shipment_day $gt_disable_cutt_off_time_ship_date_offset",
                    'id' => 'tuesday_shipment_day_gt'
                ),
                'wednesday_shipment_day_gt' => array(
                    'name' => __("", 'woocommerce-settings-gt_quotes'),
                    'type' => 'checkbox',
                    'desc' => 'Wednesday',
                    'class' => "gt_shipment_day $gt_disable_cutt_off_time_ship_date_offset",
                    'id' => 'wednesday_shipment_day_gt'
                ),
                'thursday_shipment_day_gt' => array(
                    'name' => __("", 'woocommerce-settings-gt_quotes'),
                    'type' => 'checkbox',
                    'desc' => 'Thursday',
                    'class' => "gt_shipment_day $gt_disable_cutt_off_time_ship_date_offset",
                    'id' => 'thursday_shipment_day_gt'
                ),
                'friday_shipment_day_gt' => array(
                    'name' => __("", 'woocommerce-settings-gt_quotes'),
                    'type' => 'checkbox',
                    'desc' => 'Friday',
                    'class' => "gt_shipment_day $gt_disable_cutt_off_time_ship_date_offset",
                    'id' => 'friday_shipment_day_gt'
                ),
                //**End: Cut Off Time & Ship Date Offset

                'section_end_quote' => array(
                    'type' => 'sectionend',
                    'id' => 'wc_settings_quote_section_end'
                )
            );

            $cerasis_settings = [
                'show_delivery_estimate_cerasis' => array(
                    'name' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'title',
                    'id' => 'wc_settings_cerasis_delivery_estimate'
                ),

                'residential_delivery_options_label' => array(
                    'name' => __('Residential Delivery', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'id' => 'residential_delivery_options_label'
                ),
                'residential_delivery_cerasis' => array(
                    'name' => __('Always quote as residential delivery ', 'wc-settings-cerasis_quotes'),
                    'type' => 'checkbox',
                    'desc' => '',
                    'id' => 'wc_settings_cerasis_residential_delivery'
                ),
                // Auto-detect residential addresses notification
                'avaibility_auto_residential' => array(
                    'name' => __('Auto-detect residential addresses', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'desc' => "Click <a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/'>here</a> to add the Residential Address Detection module. (<a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/#documentation'>Learn more</a>)",
                    'id' => 'avaibility_auto_residential'
                ),
                'liftgate_delivery_options_label' => array(
                    'name' => __('Lift Gate Delivery ', 'woocommerce-settings-en_woo_addons_packages_quotes'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'id' => 'liftgate_delivery_options_label'
                ),
                'lift_gate_delivery_cerasis' => array(
                    'name' => __('Always quote lift gate delivery ', 'wc-settings-cerasis_quotes'),
                    'type' => 'checkbox',
                    'desc' => '',
                    'id' => 'wc_settings_cerasis_lift_gate_delivery',
                    'class' => 'accessorial_service checkbox_fr_add',
                ),
                'cerasis_freights_liftgate_delivery_as_option' => array(
                    'name' => __('Offer lift gate delivery as an option ', 'cerasis_freights_wc_settings'),
                    'type' => 'checkbox',
                    'desc' => __('', 'cerasis_freights_wc_settings'),
                    'id' => 'cerasis_freights_liftgate_delivery_as_option',
                    'class' => 'accessorial_service checkbox_fr_add',
                ),
                // Use my liftgate notification
                'avaibility_lift_gate' => array(
                    'name' => __('Always include lift gate delivery when a residential address is detected', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'desc' => "Click <a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/'>here</a> to add the Residential Address Detection module. (<a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/#documentation'>Learn more</a>)",
                    'id' => 'avaibility_lift_gate'
                ),
                'section_cerasis_temp_end_quote' => array(
                    'type' => 'sectionend',
                    'id' => 'section_cerasis_temp_end_quote'
                )
            ];

            $end_common_settings = [
                'section_end_common_title_quote' => array(
                    'title' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'section_end_common_title_quote'
                ),

                // Handling Weight
                'engtz_label_handling_unit' => array(
                    'name' => __('Handling Unit ', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'id' => 'engtz_label_handling_unit'
                ),
                'engtz_freight_handling_weight' => array(
                    'name' => __('Weight of Handling Unit  ', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'desc' => 'Enter in pounds the weight of your pallet, skid, crate or other type of handling unit.',
                    'id' => 'engtz_freight_handling_weight'
                ),
                // max Handling Weight
                'engtz_freight_maximum_handling_weight' => array(
                    'name' => __('Maximum Weight per Handling Unit  ', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'desc' => 'Enter in pounds the maximum weight that can be placed on the handling unit.',
                    'id' => 'engtz_freight_maximum_handling_weight'
                ),
                'hand_free_mark_up_cerasis' => array(
                    'name' => __('Handling Fee / Markup ', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'desc' => 'Amount excluding tax. Enter an amount, e.g 3.75, or a percentage, e.g, 5%. Leave blank to disable.',
                    'id' => 'wc_settings_cerasis_hand_free_mark_up'
                ),
                'wc_settings_gtz_enable_logs' => array(
                    'name' => __("Enable Logs  ", 'woocommerce-settings-freightview'),
                    'type' => 'checkbox',
                    'desc' => 'When checked, the Logs page will contain up to 25 of the most recent transactions.',
                    'id' => 'wc_settings_gtz_enable_logs'
                ),
                'allow_for_own_arrangment_cerasis' => array(
                    'name' => __('Allow For Own Arrangement ', 'wc-settings-cerasis_quotes'),
                    'type' => 'checkbox',
                    'desc' => __('<span class="description">Adds an option in the shipping cart for users to indicate that they will make and pay for their own LTL shipping arrangements.</span>', 'wc-settings-cerasis_quotes'),
                    'id' => 'wc_settings_cerasis_allow_for_own_arrangment'
                ),
                'text_for_own_arrangment_cerasis' => array(
                    'name' => __('Text For Own Arrangement ', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'desc' => '',
                    'default' => "I'll arrange my own freight",
                    'id' => 'wc_settings_cerasis_text_for_own_arrangment'
                ),
                'allow_other_plugins' => array(
                    'name' => __('Show WooCommerce Shipping Options ', 'wc-settings-cerasis_quotes'),
                    'type' => 'select',
                    'default' => '3',
                    'desc' => __('Enabled options on WooCommerce Shipping page are included in quote results.', 'wc-settings-cerasis_quotes'),
                    'id' => 'wc_settings_cerasis_allow_other_plugins',
                    'options' => array(
                        'yes' => __('YES', 'YES'),
                        'no' => __('NO', 'NO'),
                    )
                ),
                'allow_flate_rate' => array(
                    'name' => __('Show WooCommerce Flat Rate ', 'wc-settings-cerasis_quotes'),
                    'type' => 'select',
                    'default' => '3',
                    'desc' => __('Offer flat rate, if no quotes are returned from the App', 'wc-settings-cerasis_quotes'),
                    'id' => 'wc_settings_cerasis_allow_flate_rate',
                    'options' => array(
                        'yes' => __('YES', 'YES'),
                        'no' => __('NO', 'NO'),
                    )
                ),
                'return_LTL_quotes_cerasis' => array(
                    'name' => __("Return LTL quotes when an order parcel shipment weight exceeds the weight threshold  ", 'wc-settings-cerasis_quotes'),
                    'type' => 'checkbox',
                    'desc' => '<span class="description" >When checked, the LTL Freight Quote will return quotes when an order’s total weight exceeds the weight threshold (the maximum permitted by WWE and UPS), even if none of the products have settings to indicate that it will ship LTL Freight. To increase the accuracy of the returned quote(s), all products should have accurate weights and dimensions. </span>',
                    'id' => 'en_plugins_return_LTL_quotes'
                ),
                // Weight threshold for LTL freight
                'en_weight_threshold_lfq' => [
                    'name' => __('Weight threshold for LTL Freight Quotes  ', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'default' => $weight_threshold,
                    'class' => $weight_threshold_class,
                    'id' => 'en_weight_threshold_lfq'
                ],
                'en_suppress_parcel_rates' => array(
                    'name' => __("", 'wc-settings-cerasis_quotes'),
                    'type' => 'radio',
                    'default' => 'display_parcel_rates',
                    'options' => array(
                        'display_parcel_rates' => __("Continue to display parcel rates when the weight threshold is met.", 'woocommerce'),
                        'suppress_parcel_rates' => __("Suppress parcel rates when the weight threshold is met.", 'woocommerce'),
                    ),
                    'class' => 'en_suppress_parcel_rates',
                    'id' => 'en_suppress_parcel_rates',
                ),
                // Error management
                'error_management_gtz_ltl' => array(
                    'name' => __('Error management ', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'id' => 'error_management_gtz_ltl',
                    'class' => 'hidden',
                ),
                'error_management_settings_gtz_ltl' => array(
                    'name' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'radio',
                    'default' => 'quote_shipping',
                    'options' => array(
                        'quote_shipping' => __('Quote shipping using known shipping parameters, even if other items are missing shipping parameters.', 'woocommerce'),
                        'dont_quote_shipping' => __('Don\'t quote shipping if one or more items are missing the required shipping parameters.', 'woocommerce'),
                    ),
                    'id' => 'error_management_settings_gtz_ltl',
                ),
                // Backup Rates
                'backup_rates_gtz_ltl' => array(
                    'name' => __('Checkout options if the plugin fails to return a rate ', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'desc' => __('', 'wc-settings-cerasis_quotes'),
                    'id' => 'backup_rates_gtz_ltl'
                ),
                'enable_backup_rates_gtz_ltl' => array(
                    'name' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'checkbox',
                    'desc' => __('Present the user with a backup shipping rate.', 'wc-settings-cerasis_quotes'),
                    'id' => 'enable_backup_rates_gtz_ltl',
                ),
                'gtz_ltl_backup_rates_label' => array(
                    'name' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'desc' => 'Label for backup shipping rate (Maximum of 50 characters).',
                    'id' => 'gtz_ltl_backup_rates_label'
                ),
                'gtz_ltl_backup_rates_category' => array(
                    'name' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'radio',
                    'default' => 'fixed_rate',
                    'options' => array(
                        'fixed_rate' => __('', 'woocommerce'),
                        'percentage_of_cart_price' => __('', 'woocommerce'),
                        'function_of_weight' => __('', 'woocommerce'),
                    ),
                    'id' => 'gtz_ltl_backup_rates_category',
                ),
                'gtz_ltl_backup_rates_carrier_fails_to_return_response' => array(
                    'name' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'checkbox',
                    'desc' => __('Display the backup rate if the carrier fails to return a response.', 'wc-settings-cerasis_quotes'),
                    'id' => 'gtz_ltl_backup_rates_carrier_fails_to_return_response',
                ),
                'gtz_ltl_backup_rates_carrier_returns_error' => array(
                    'name' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'checkbox',
                    'desc' => __('Display the backup rate if the carrier returns an error.', 'wc-settings-cerasis_quotes'),
                    'id' => 'gtz_ltl_backup_rates_carrier_returns_error',
                ),
                'gtz_ltl_backup_rates_display' => array(
                    'name' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'radio',
                    'default' => 'no_other_rates',
                    'options' => array(
                        'no_plugin_rates' => __('Display the backup rate if the plugin fails to return a rate.', 'woocommerce'),
                        'no_other_rates' => __('Display the backup rate only if no rates, from any shipping method, are presented.', 'woocommerce'),
                    ),
                    'id' => 'gtz_ltl_backup_rates_display',
                ),
                'section_end_common_quote' => array(
                    'type' => 'sectionend',
                    'id' => 'wc_settings_quote_section_end_common'
                )
            ];

            if ($cerasis_global_tranz_api_endpoint == 'wc_global_tranz_api_fields' || $cerasis_global_tranz_api_endpoint == 'wc_global_tranz_new_api_fields') {
                $notify_and_limtied_delivery_settings = [
                    'show_notify_delivery_cerasis' => array(
                        'name' => __('', 'wc-settings-cerasis_quotes'),
                        'type' => 'title',
                        'id' => 'wc_settings_cerasis_notify_delivery'
                    ),

                    // Globaltranz notify
                    'notify_delivery_options_label' => array(
                        'name' => __('Notify Before Delivery ', 'woocommerce-settings-globaltranz'),
                        'type' => 'text',
                        'class' => 'hidden',
                        'id' => 'notify_delivery_options_label'
                    ),
                    'notify_delivery_cerasis' => array(
                        'name' => __('Always quote notify before delivery ', 'woocommerce-settings-globaltranz'),
                        'type' => 'checkbox',
                        'id' => 'wc_settings_cerasis_notify',
                        'class' => 'accessorial_service checkbox_notify',
                    ),
                    'cerasis_freights_notify_as_option' => array(
                        'name' => __('Offer notify before delivery as an option  ', 'woocommerce-settings-globaltranz'),
                        'type' => 'checkbox',
                        'id' => 'cerasis_freights_notify_as_option',
                        'class' => 'accessorial_service checkbox_notify',
                    ),
                    // Limited access delivery
                    'gtz_limited_access_delivery_label' => array(
                        'name' => __("Limited Access Delivery", 'woocommerce-settings-en_woo_addons_packages_quotes'),
                        'type' => 'text',
                        'class' => 'hidden',
                        'desc' => '',
                        'id' => 'gtz_limited_access_delivery_label'
                    ),
                    'gtz_limited_access_delivery' => array(
                        'name' => __("Always quote limited access delivery", 'woocommerce-settings-en_woo_addons_packages_quotes'),
                        'type' => 'checkbox',
                        'id' => 'gtz_limited_access_delivery',
                        'class' => "accessorial_service gtz_limited_access_add",
                    ),
                    'gtz_limited_access_delivery_as_option' => array(
                        'name' => __("Offer limited access delivery as an option", 'woocommerce-settings-en_woo_addons_packages_quotes'),
                        'type' => 'checkbox',
                        'id' => 'gtz_limited_access_delivery_as_option',
                        'class' => "accessorial_service gtz_limited_access_add",
                    ),
                    'gtz_limited_access_delivery_fee' => array(
                        'name' => __("Limited access delivery fee", 'woocommerce-settings-en_woo_addons_packages_quotes'),
                        'type' => 'text',
                        'id' => 'gtz_limited_access_delivery_fee',
                        'class' => "accessorial_service",
                    ),
                    'section_cerasis_notify_temp_end_quote' => array(
                        'type' => 'sectionend',
                        'id' => 'section_cerasis_notify_temp_end_quote'
                    )
                ];

                $end_common_settings = $start_common_settings + $cerasis_settings + $notify_and_limtied_delivery_settings + $end_common_settings;
            } else {
                $end_common_settings = $start_common_settings + $cerasis_settings + $end_common_settings;
            }

            $settings = $cerasis_temp + $end_common_settings;

            return $settings;
        }

    }

}