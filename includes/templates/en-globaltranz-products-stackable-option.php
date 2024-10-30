<?php

/**
 *  Box sizes template 
 */
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists("UpdateProducStackabletDetailOption")) {

    class UpdateProducStackabletDetailOption {

        /**
         * Constructor.
         */
        public function __construct($action) {

            if ($action == 'hooks') {
                $this->engtz_add_simple_product_hooks();
                $this->engtz_add_variable_product_hooks();
            }
        }

        /**
         * Add simple product fields.
         */
        public function engtz_add_simple_product_hooks() {

            /* Add simple product fields */
            add_action(
                    'woocommerce_product_options_shipping', array($this, 'engtz_show_product_fields'), 100
            );
            add_action(
                    'woocommerce_process_product_meta', array($this, 'engtz_save_product_fields'), 10
            );
        }

        /**
         * Add variable product fields.
         */
        public function engtz_add_variable_product_hooks() {

            add_action(
                    'woocommerce_product_after_variable_attributes', array($this, 'engtz_show_product_fields'), 100, 3
            );
            add_action(
                    'woocommerce_save_product_variation', array($this, 'engtz_save_product_fields'), 10
            );
        }

        /**
         * Save the simple product fields.
         * @param int $post_id
         */
        function engtz_save_product_fields($post_id) {

            if (isset($post_id) && $post_id > 0) {
                $var_hazardous = ( isset($_POST['_stackable'][$post_id]) ) ? sanitize_text_field($_POST['_stackable'][$post_id]) : "";
                update_post_meta(
                        $post_id, '_stackable', esc_attr($var_hazardous)
                );
            }
        }

        /**
         * Show product fields in variation and simple product.
         * @param array $loop
         * @param object $variation_data
         * @param object $variation
         */
        function engtz_show_product_fields($loop, $variation_data = array(), $variation = array()) {

            if (!empty($variation) || isset($variation->ID)) {
                /* Variable products */
                $this->engtz_product_custom_fields($variation->ID);
            } else {
                /* Simple products */
                $post_id = get_the_ID();
                $this->engtz_product_custom_fields($post_id);
            }
        }
        
        /**
         * Add vertival rotation checkbox.
         * @global $wpdb
         * @param $loop
         * @param $variation_data
         * @param $variation
         */
        function engtz_product_custom_fields($post_id) {
            
                $description = "";
                $disable_stackable = "";

                $plan_notifi = apply_filters('engtz_plans_notification_PD' , array());

                if(!empty($plan_notifi) && (isset($plan_notifi['stackable_option'])))
                {
                    $enable_plugins = (isset($plan_notifi['stackable_option']['enable_plugins'])) ? $plan_notifi['stackable_option']['enable_plugins'] : "";
                    $disable_plugins = (isset($plan_notifi['stackable_option']['disable_plugins'])) ? $plan_notifi['stackable_option']['disable_plugins'] : "";
                    if(strlen($disable_plugins) > 0)
                    {
                        if(strlen($enable_plugins) > 0)
                        {
                            $description =  apply_filters('engtz_plans_notification_message_action' , $enable_plugins , $disable_plugins);
                        }
                        else
                        {
                            $description = apply_filters('globaltranz_plans_notification_link' , array(2));
                            $disable_stackable = "disabled_me";
                        }
                    }
                }
                
                $field_array = array(
                    'id' => '_stackable[' . $post_id . ']',
                    'label' => __(
                            'Stackable Option', 'woocommerce'
                    ),
                    'class' =>  "$disable_stackable _en_stackable_option",
                    'value' => get_post_meta(
                            $post_id, '_stackable', true
                    ),

                    'description' => __(
                            "$description", 'woocommerce'
                    )
                );
                woocommerce_wp_checkbox($field_array);
        }

    }

    /* Initialize object */
    new UpdateProducStackabletDetailOption('hooks');
}