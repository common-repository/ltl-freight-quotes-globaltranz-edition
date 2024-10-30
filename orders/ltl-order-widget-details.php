<?php

/**
 * WWE LTL Group Packaging
 *
 * @package     WWE LTL Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists("Engtz_view_Order_Widget_Details")) {

    class Engtz_view_Order_Widget_Details
    {

        public $sender_origin;
        public $accessorials;
        public $label_sufex;
        public $product_name;
        public $count;
        public $_address;

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->engtz_call_hooks();
        }

        /**
         * Call needed hooks.
         */
        public function engtz_call_hooks()
        {

            /* Woocommerce order action hook */
            add_action(
                'woocommerce_order_actions', array($this, 'engtz_assign_order_details'), 10, 2
            );
        }

        /**
         * Adding Meta container admin shop_order pages
         * @param $actions
         */
        function engtz_create_meta_box_order_details()
        {
            $this->engtz_assign_order_details();
        }

        /**
         * Get order details from meta data.
         */
        function engtz_assign_order_details($actions, $order)
        {
            global $wpdb;
            $this->shipment_status = 'single';

            $this->label_sufex = array();
            $this->accessorials = array();

            $this->order_key = $order->get_order_key();
            $shipping_details = $order->get_items('shipping');
            foreach ($shipping_details as $item_id => $shipping_item_obj) {
                $this->shipping_method_title = $shipping_item_obj->get_method_title() . ' ' . ' : ';
                $this->_shipping_method_title = $shipping_item_obj->get_method_title() . ' ' . ' ';
                $this->shipping_method_total = $shipping_item_obj->get_total();

                $this->result_details = $shipping_item_obj->get_formatted_meta_data();
            }
            /* Add metabox if user selected our service */
            if (!empty($this->result_details) && count($this->result_details) > 0) {
                /* Add metabox for 3dbin visual details */
                add_meta_box(
                    'en_additional_order_details', __('Additional Order Details', 'woocommerce'), array($this, 'engtz_add_meta_box_order_widget'), get_current_screen()->id, 'side', 'low', 'core');
            }

            return $actions;
        }

        /**
         * Add order details in metabox.
         */
        public function engtz_add_meta_box_order_widget()
        {
            $order_details = $this->result_details;
            $this->engtz_origin_services_details($order_details);
        }

        /**
         * Origin & Services details.
         * @param array $order_data
         * @param string $shipment_status
         * @param int $ship_count
         * @param array $single_price_details
         */
        function engtz_origin_services_details($order_data)
        {
            $en_flat_rate_total = 0;
            $en_flat_rate_details = [];
            $handling_fee = get_option('wc_settings_wwe_hand_free_mark_up');
            $shipping_total = $this->shipping_method_total;

            $this->currency_symbol = get_woocommerce_currency_symbol(get_option('woocommerce_currency'));
            $this->count = 0;
            $min_prices = [];
            $shipment = 'single';
            foreach ($order_data as $key => $is_meta_data) {
                if (isset($is_meta_data->key) && $is_meta_data->key === "min_prices") {
                    $shipment = 'multiple';
                    $min_prices = $is_meta_data->value;
                }

                if ($is_meta_data->key == "en_flat_rate_details") {
                    $en_flat_rate_details = json_decode($is_meta_data->value);
                }

                if ($is_meta_data->key == "en_flat_rate_total") {
                    $en_flat_rate_total = $is_meta_data->value;
                }
            }

            if ($shipment == 'multiple') {
                if (!empty($min_prices)) {
                    $order_data = json_decode($min_prices, TRUE);
                    foreach ($order_data as $key => $quote) {
                        $this->sender_origin = (isset($quote['meta_data']['sender_origin'])) ? ucwords($quote['meta_data']['sender_origin']) : "";
                        $this->accessorials = (isset($quote['meta_data']['accessorials'])) ? json_decode($quote['meta_data']['accessorials'], TRUE) : array();
                        $this->product_name = (isset($quote['meta_data']['product_name'])) ? json_decode($quote['meta_data']['product_name'], TRUE) : array();
                        $this->_address = (isset($quote['meta_data']['_address'])) ? $quote['meta_data']['_address'] : array();
                        $this->label_sufex = (isset($quote['label_sufex'])) ? $quote['label_sufex'] : array();
                        $this->shipping_method_total = (isset($quote['cost'])) ? $quote['cost'] : "";

                        $_label = (isset($quote['label']) && strlen($quote['label']) > 0) ? $quote['label'] : "Freight";

                        $_label_append = " : ";
                        if (isset($quote['code']) && ($quote['code'] == "no_quotes")) {
                            $_label = "";
                            $_label_append = "";
                        }

                        $this->shipping_method_title = $this->engtz_filter_from_label_sufexl($this->label_sufex, $_label) . $_label_append;
                        $this->shipping_method_total = (isset($quote['cost'])) ? $quote['cost'] : "";
                        $this->count++;
                        $this->engtz_show_order_widget_detail();
                    }
                }
            } else {

                foreach ($order_data as $key => $value) {
                    (isset($value->key) && $value->key == "sender_origin") ? $this->sender_origin = ucwords($value->value) : "";
                    (isset($value->key) && $value->key == "accessorials") ? $this->accessorials = json_decode($value->value, TRUE) : "";
                    (isset($value->key) && $value->key == "label_sufex") ? $this->label_sufex = json_decode($value->value, TRUE) : "";
                    (isset($value->key) && $value->key == "product_name") ? $this->product_name = json_decode($value->value, TRUE) : "";
                    (isset($value->key) && $value->key == "cost") ? $this->shipping_method_total = json_decode($value->value, TRUE) : "";

                }

                if ($en_flat_rate_total > 0) {
                    $this->shipping_method_total -= $en_flat_rate_total;
                }

                $this->count++;
                $this->engtz_show_order_widget_detail();
            }

            if (!empty($en_flat_rate_details) && $en_flat_rate_total > 0) {
                $this->sender_origin = 'Flat Rate Shipping';
                $this->shipping_method_title = 'Flat Rate';
                $this->product_name = $en_flat_rate_details;
                $this->shipping_method_total = $en_flat_rate_total;
                $this->label_sufex = $this->accessorials = [];
                $this->engtz_show_order_widget_detail();
            }
        }

        /**
         * Show Order Detai on order page
         */
        public function engtz_show_order_widget_detail()
        {
            if (!(isset($this->sender_origin) && strlen($this->sender_origin) > 0)) {
                return;
            }

            echo '<h4 style="text-decoration: underline;margin: 4px 0px 4px 0px;">Shipment ' . $this->count . " > Origin & Services </h4>";
            echo '<ul class="en-list" style="list-style: disc;list-style-position: inside;">';
            echo '<li>';

            echo $this->sender_origin;

            echo '<br />';

            echo '</li>';

            if (isset($this->_address) && is_string($this->_address) && strlen($this->_address) > 0) {
                echo '<li>' . $this->_shipping_method_title . $this->_address . ' ' . $this->engtz_format_price($this->shipping_method_total) . '</li>';
            } else {
                echo '<li>' . $this->shipping_method_title . $this->engtz_format_price($this->shipping_method_total) . '</li>';
            }

            /* Show accessorials */
            $this->engtz_show_accessorials(array_unique(array_merge($this->accessorials, $this->label_sufex)));


            echo "</ul>";
            echo "<br />";
            echo '<h4 style="    text-decoration: underline;margin: 4px 0px 4px 0px;">Shipment ' . $this->count . " > items </h4>";
            echo '<ul id="product-details-order" class="en-list" style="list-style: disc;list-style-position: inside;">';

            foreach (array_filter($this->product_name) as $product_str) {
                echo '<li>' . $product_str . '</li>';
            }

            echo '</ul>';
            echo "<br /><br />";
        }

        /**
         * set accessorials in label of rate
         * @param type $label_sufex
         * @return string
         */
        public function engtz_filter_from_label_sufexl($label_sufex, $append_label)
        {
            $rad_status = true;
            $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
            if (stripos(implode($all_plugins), 'residential-address-detection.php') || is_plugin_active_for_network('residential-address-detection/residential-address-detection.php')) {
                if(get_option('suspend_automatic_detection_of_residential_addresses') != 'yes') {
                    $rad_status = get_option('residential_delivery_options_disclosure_types_to') != 'not_show_r_checkout';
                }
            }
            switch (TRUE) {
                case(count($label_sufex) == 1):
                    (in_array('L', $label_sufex)) ? $append_label .= " with lift gate delivery " : "";
                    (in_array('R', $label_sufex) && $rad_status == true) ? $append_label .= " with residential delivery " : "";
                    break;
                case(count($label_sufex) == 2):
                    (in_array('L', $label_sufex)) ? $append_label .= " with lift gate delivery " : "";
                    (in_array('R', $label_sufex) && $rad_status == true) ? $append_label .= (strlen($append_label) > 0) ? " and residential delivery " : " with residential delivery " : "";
                    break;
            }

            return $append_label;
        }

        /**
         * Price format.
         * @param int/double/string $dollars
         * @return string
         */
        function engtz_format_price($dollars)
        {
            return $this->currency_symbol . number_format(sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $dollars)), 2);
        }

        /**
         * Show accessorial on order detail page.
         */
        public function engtz_show_accessorials($service_order_data)
        {
            foreach ($service_order_data as $key => $value) {
                echo ($value == "R") ? '<li>Residential delivery</li>' : "";
                echo ($value == "L") ? '<li>Lift gate delivery</li>' : "";
                echo ($value == "H") ? '<li>Hazardous Material</li>' : "";
                echo ($value == "S") ? '<li>Stackable Material</li>' : "";
                echo ($value == "HAT") ? '<li>Hold At Terminal</li>' : "";
                echo ($value == "N") ? '<li>Notify before delivery</li>' : "";
                echo ($value == "A") ? '<li>Limited access delivery</li>' : "";
            }
        }

    }

    /* Initialize class object */
    new Engtz_view_Order_Widget_Details();
}
    
