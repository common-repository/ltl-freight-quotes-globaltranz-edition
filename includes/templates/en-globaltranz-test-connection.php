<?php

/**
 * WC Cerasis Connection Settings Tab Class
 * @package     Woocommerce GlobalTranz Edition
 * @author      <https://eniture.com/>
 * @version     v.1..0 (01/10/2017)
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cerasis Test connection Settings Form Class
 */
if (!class_exists('Engtz_Cerasis_Test_Connection_Page')) {

    class Engtz_Cerasis_Test_Connection_Page
    {

        /**
         * test connection setting form
         * @return array
         */
        function connection_setting_tab()
        {

            $settings = array(
                'section_title_wc_cerasis' => array(
                    'name' => __('', 'wc-settings-cerasis_quotes'),
                    'type' => 'title',
                    'desc' => '<br> ',
                    'id' => 'wc_settings_cerasis_title_section_connection'
                ),
                'cerasis_global_tranz_api_endpoint' => array(
                    'name' => __('Which API will you connect to? ', 'woocommerce-settings-freightquote-quotes'),
                    'type' => 'select',
                    'default' => 'wc_cerasis_api_fields',
                    'id' => 'cerasis_global_tranz_api_endpoint',
                    'options' => array(
                        'wc_cerasis_api_fields' => __('Cerasis', 'Cerasis'),
                        'wc_global_tranz_api_fields' => __('GlobalTranz', 'GlobalTranz'),
                        'wc_global_tranz_new_api_fields' => __('New API', 'New API'),
                    )
                ),
                // Global tranz
                'wc_global_tranz_customer_id' => array(
                    'name' => __('Customer ID ', 'wc-settings-global_tranz_quotes'),
                    'type' => 'text',
                    'desc' => __('', 'wc-settings-global_tranz_quotes'),
                    'id' => 'wc_settings_global_tranz_customer_id',
                    'class' => 'wc_global_tranz_api_fields',
                    'placeholder' => 'Customer ID'
                ),
                'wc_global_tranz_username' => array(
                    'name' => __('Username ', 'wc-settings-global_tranz_quotes'),
                    'type' => 'text',
                    'desc' => __('', 'wc-settings-global_tranz_quotes'),
                    'id' => 'wc_settings_global_tranz_username',
                    'class' => 'wc_global_tranz_api_fields',
                    'placeholder' => 'Username'
                ),
                'wc_global_tranz_password' => array(
                    'name' => __('Password ', 'wc-settings-global_tranz_quotes'),
                    'type' => 'text',
                    'desc' => __('', 'wc-settings-global_tranz_quotes'),
                    'id' => 'wc_settings_global_tranz_password',
                    'class' => 'wc_global_tranz_api_fields',
                    'placeholder' => 'Password'
                ),
                'wc_global_tranz_authentication_key' => array(
                    'name' => __('Access Key ', 'wc-settings-global_tranz_quotes'),
                    'type' => 'text',
                    'desc' => __('', 'wc-settings-global_tranz_quotes'),
                    'id' => 'wc_settings_global_tranz_authentication_key',
                    'class' => 'wc_global_tranz_api_fields',
                    'placeholder' => 'Access Key'
                ),
                // Cerasis
                'wc_cerasis_shipper_id' => array(
                    'name' => __('Shipper ID ', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'desc' => __('', 'wc-settings-cerasis_quotes'),
                    'id' => 'wc_settings_cerasis_shipper_id',
                    'class' => 'wc_cerasis_api_fields',
                    'placeholder' => 'Shipper ID'
                ),
                'wc_cerasis_username' => array(
                    'name' => __('Username ', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'desc' => __('', 'wc-settings-cerasis_quotes'),
                    'id' => 'wc_settings_cerasis_username',
                    'class' => 'wc_cerasis_api_fields',
                    'placeholder' => 'Username'
                ),
                'wc_cerasis_password' => array(
                    'name' => __('Password ', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'desc' => __('', 'wc-settings-cerasis_quotes'),
                    'id' => 'wc_settings_cerasis_password',
                    'class' => 'wc_cerasis_api_fields',
                    'placeholder' => 'Password'
                ),
                'wc_cerasis_authentication_key' => array(
                    'name' => __('Access Key ', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'desc' => __('', 'wc-settings-cerasis_quotes'),
                    'id' => 'wc_settings_cerasis_authentication_key',
                    'class' => 'wc_cerasis_api_fields',
                    'placeholder' => 'Access Key'
                ),

                // New API
                'wc_gtz_new_api_client_id' => array(
                    'name' => __('Client ID ', 'wc-settings-new_api_quotes'),
                    'type' => 'text',
                    'desc' => __('', 'wc-settings-new_api_quotes'),
                    'id' => 'wc_gtz_new_api_client_id',
                    'class' => 'wc_global_tranz_new_api_fields'
                ),
                'wc_gtz_new_api_client_secret' => array(
                    'name' => __('Client Secret ', 'wc-settings-new_api_quotes'),
                    'type' => 'text',
                    'desc' => __('', 'wc-settings-new_api_quotes'),
                    'id' => 'wc_gtz_new_api_client_secret',
                    'class' => 'wc_global_tranz_new_api_fields'
                ),
                'wc_gtz_new_api_api_username' => array(
                    'name' => __('Username ', 'wc-settings-new_api_quotes'),
                    'type' => 'text',
                    'desc' => __('', 'wc-settings-new_api_quotes'),
                    'id' => 'wc_gtz_new_api_api_username',
                    'class' => 'wc_global_tranz_new_api_fields'
                ),
                'wc_gtz_new_api_api_password' => array(
                    'name' => __('Password ', 'wc-settings-new_api_quotes'),
                    'type' => 'text',
                    'desc' => __('', 'wc-settings-new_api_quotes'),
                    'id' => 'wc_gtz_new_api_api_password',
                    'class' => 'wc_global_tranz_new_api_fields'
                ),

                'wc_cerasis_plugin_licence_key' => array(
                    'name' => __('Eniture API Key ', 'wc-settings-cerasis_quotes'),
                    'type' => 'text',
                    'desc' => __('Obtain a Eniture API Key from <a href="https://eniture.com/woocommerce-globaltranz-ltl-freight/" target="_blank" >eniture.com </a>', 'wc-settings-cerasis_quotes'),
                    'id' => 'wc_settings_cerasis_licence_key',
                    'class' => 'wc_cerasis_api_fields wc_global_tranz_api_fields wc_global_tranz_new_api_fields',
                    'placeholder' => 'Eniture API Key'
                ),
                'wc_cerasis_save_buuton' => array(
                    'name' => __('Save Button ', 'wc-settings-cerasis_quotes'),
                    'type' => 'button',
                    'desc' => __('', 'wc-settings-cerasis_quotes'),
                    'id' => 'wc_settings_cerasis_button'
                ),
                'wc_cerasis_section_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wc_settings_cerasis_end-section_connection'
                ),
            );
            return $settings;
        }

    }

}