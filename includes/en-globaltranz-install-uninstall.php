<?php

/**
 * LTL Freight Plugin Installation |  GlobalTranz Edition
 * @package     Woocommerce GlobalTranz Edition
 * @author      <https://eniture.com/>
 * @version     v.1..0 (01/10/2017)
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}
/**
 * LTL Freight Plugin Installation |  GlobalTranz Edition
 */
if (!class_exists('Engtz_Install_Uninstall')) {

    class Engtz_Install_Uninstall
    {
        public function __construct()
        {
            // Origin terminal address
            add_action('admin_init', [$this, 'gt_freight_update_warehouse']);
        }

        /**
         * Plugin installation script
         */
        public static function install($network_wide = null)
        {
            if ( is_multisite() && $network_wide ) {

                foreach (get_sites(['fields'=>'ids']) as $blog_id) {
                    switch_to_blog($blog_id);
                    global $wpdb;
                    add_option('wc_cerasis_edition', '1.0.0', '', 'yes');
                    add_option('wc_cerasis_db_version', '1.0.0');
                    $eniture_plugins = get_option('EN_Plugins');
                    if (!$eniture_plugins) {
                        add_option('EN_Plugins', json_encode(array('engtz_cerasis_shipping_method')));
                    } else {
                        $plugins_array = json_decode($eniture_plugins, true);
                        if (!in_array('engtz_cerasis_shipping_method', $plugins_array)) {
                            array_push($plugins_array, 'engtz_cerasis_shipping_method');
                            update_option('EN_Plugins', json_encode($plugins_array));
                        }
                    }
                    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                    //carriers table
                    $carriers_table = $wpdb->prefix . "en_cerasis_account_carriers";
                    if ($wpdb->query("SHOW TABLES LIKE '" . $carriers_table . "'") === 0) {
                        $sql = "CREATE TABLE $carriers_table (
                id int(10) NOT NULL AUTO_INCREMENT,
                carrier_scac varchar(600) NOT NULL,
                carrier_name varchar(600) NOT NULL,
                carrier_logo varchar(255) NOT NULL,
                carrier_status varchar(8) NOT NULL,
                plugin_name varchar(100) NOT NULL,
                liftgate_fee varchar(255) NOT NULL,
                PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
                        dbDelta($sql);
                    }
                    $gt_carriers_table = $wpdb->prefix . "gt_carriers";
                    if ($wpdb->query("SHOW TABLES LIKE '" . $gt_carriers_table . "'") === 0) {
                        $sql = "CREATE TABLE $gt_carriers_table (
                id int(10) NOT NULL AUTO_INCREMENT,
                gtz_scac varchar(600) NOT NULL,
                gtz_name varchar(600) NOT NULL,
                logo varchar(255) NOT NULL,
                carrier_status varchar(8) NOT NULL,
                PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
                        dbDelta($sql);
                    }

                    //Alter Table
                    $enable_store_pickup_col = $wpdb->get_row("SHOW COLUMNS FROM " . $carriers_table . " LIKE 'liftgate_fee'");
                    if (!(isset($enable_store_pickup_col->Field) && $enable_store_pickup_col->Field == 'liftgate_fee')) {
                        $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN liftgate_fee VARCHAR(255) NOT NULL", $carriers_table));
                    }

                    //warehouse table
                    $warehouse_table = $wpdb->prefix . "warehouse";
                    if ($wpdb->query("SHOW TABLES LIKE '" . $warehouse_table . "'") === 0) {
                        $origin = 'CREATE TABLE ' . $warehouse_table . '(
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                city varchar(200) NOT NULL,
                state varchar(200) NOT NULL,
                address varchar(255) NOT NULL,
                phone_instore varchar(255) NOT NULL,
                zip varchar(200) NOT NULL,
                country varchar(200) NOT NULL,
                location varchar(200) NOT NULL,
                nickname varchar(200) NOT NULL,
                enable_store_pickup VARCHAR(255) NOT NULL,
                miles_store_pickup VARCHAR(255) NOT NULL ,
                match_postal_store_pickup VARCHAR(255) NOT NULL ,
                checkout_desc_store_pickup VARCHAR(255) NOT NULL ,
                enable_local_delivery VARCHAR(255) NOT NULL ,
                miles_local_delivery VARCHAR(255) NOT NULL ,
                match_postal_local_delivery VARCHAR(255) NOT NULL ,
                checkout_desc_local_delivery VARCHAR(255) NOT NULL ,
                fee_local_delivery VARCHAR(255) NOT NULL ,
                suppress_local_delivery VARCHAR(255) NOT NULL,
                origin_markup VARCHAR(15),
                PRIMARY KEY  (id) )';
                        dbDelta($origin);
                    }

                    $enable_store_pickup_col = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'enable_store_pickup'");
                    if (!(isset($enable_store_pickup_col->Field) && $enable_store_pickup_col->Field == 'enable_store_pickup')) {
                        $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN enable_store_pickup VARCHAR(255) NOT NULL , "
                            . "ADD COLUMN miles_store_pickup VARCHAR(255) NOT NULL , "
                            . "ADD COLUMN match_postal_store_pickup VARCHAR(255) NOT NULL , "
                            . "ADD COLUMN checkout_desc_store_pickup VARCHAR(255) NOT NULL , "
                            . "ADD COLUMN enable_local_delivery VARCHAR(255) NOT NULL , "
                            . "ADD COLUMN miles_local_delivery VARCHAR(255) NOT NULL , "
                            . "ADD COLUMN match_postal_local_delivery VARCHAR(255) NOT NULL , "
                            . "ADD COLUMN checkout_desc_local_delivery VARCHAR(255) NOT NULL , "
                            . "ADD COLUMN fee_local_delivery VARCHAR(255) NOT NULL , "
                            . "ADD COLUMN suppress_local_delivery VARCHAR(255) NOT NULL", $warehouse_table));
                    }

                    $gtz_origin_markup = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'origin_markup'");
                    if (!(isset($gtz_origin_markup->Field) && $gtz_origin_markup->Field == 'origin_markup')) {
                        $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN origin_markup VARCHAR(15)", $warehouse_table));
                    }

                    $engtz_cerasis_carrier_list = new Engtz_Cerasis_Carrier_List();
                    $engtz_cerasis_carrier_list->carriers();

                    $engtz_carrier_list = new gt_ltl_carriers();
                    $engtz_carrier_list->gt_carriers();
                    restore_current_blog();
                }

            } else {
                global $wpdb;
                add_option('wc_cerasis_edition', '1.0.0', '', 'yes');
                add_option('wc_cerasis_db_version', '1.0.0');
                $eniture_plugins = get_option('EN_Plugins');
                if (!$eniture_plugins) {
                    add_option('EN_Plugins', json_encode(array('engtz_cerasis_shipping_method')));
                } else {
                    $plugins_array = json_decode($eniture_plugins, true);
                    if (!in_array('engtz_cerasis_shipping_method', $plugins_array)) {
                        array_push($plugins_array, 'engtz_cerasis_shipping_method');
                        update_option('EN_Plugins', json_encode($plugins_array));
                    }
                }
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                //carriers table
                $carriers_table = $wpdb->prefix . "en_cerasis_account_carriers";
                if ($wpdb->query("SHOW TABLES LIKE '" . $carriers_table . "'") === 0) {
                    $sql = "CREATE TABLE $carriers_table (
                id int(10) NOT NULL AUTO_INCREMENT,
                carrier_scac varchar(600) NOT NULL,
                carrier_name varchar(600) NOT NULL,
                carrier_logo varchar(255) NOT NULL,
                carrier_status varchar(8) NOT NULL,
                plugin_name varchar(100) NOT NULL,
                liftgate_fee varchar(255) NOT NULL,
                PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
                    dbDelta($sql);
                }
                $gt_carriers_table = $wpdb->prefix . "gt_carriers";
                if ($wpdb->query("SHOW TABLES LIKE '" . $gt_carriers_table . "'") === 0) {
                    $sql = "CREATE TABLE $gt_carriers_table (
                id int(10) NOT NULL AUTO_INCREMENT,
                gtz_scac varchar(600) NOT NULL,
                gtz_name varchar(600) NOT NULL,
                logo varchar(255) NOT NULL,
                carrier_status varchar(8) NOT NULL,
                PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
                    dbDelta($sql);
                }

                //Alter Table
                $enable_store_pickup_col = $wpdb->get_row("SHOW COLUMNS FROM " . $carriers_table . " LIKE 'liftgate_fee'");
                if (!(isset($enable_store_pickup_col->Field) && $enable_store_pickup_col->Field == 'liftgate_fee')) {
                    $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN liftgate_fee VARCHAR(255) NOT NULL", $carriers_table));
                }

                //warehouse table
                $warehouse_table = $wpdb->prefix . "warehouse";
                if ($wpdb->query("SHOW TABLES LIKE '" . $warehouse_table . "'") === 0) {
                    $origin = 'CREATE TABLE ' . $warehouse_table . '(
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                city varchar(200) NOT NULL,
                state varchar(200) NOT NULL,
                address varchar(255) NOT NULL,
                phone_instore varchar(255) NOT NULL,
                zip varchar(200) NOT NULL,
                country varchar(200) NOT NULL,
                location varchar(200) NOT NULL,
                nickname varchar(200) NOT NULL,
                enable_store_pickup VARCHAR(255) NOT NULL,
                miles_store_pickup VARCHAR(255) NOT NULL ,
                match_postal_store_pickup VARCHAR(255) NOT NULL ,
                checkout_desc_store_pickup VARCHAR(255) NOT NULL ,
                enable_local_delivery VARCHAR(255) NOT NULL ,
                miles_local_delivery VARCHAR(255) NOT NULL ,
                match_postal_local_delivery VARCHAR(255) NOT NULL ,
                checkout_desc_local_delivery VARCHAR(255) NOT NULL ,
                fee_local_delivery VARCHAR(255) NOT NULL ,
                suppress_local_delivery VARCHAR(255) NOT NULL,
                origin_markup VARCHAR(15),
                PRIMARY KEY  (id) )';
                    dbDelta($origin);
                }

                $enable_store_pickup_col = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'enable_store_pickup'");
                if (!(isset($enable_store_pickup_col->Field) && $enable_store_pickup_col->Field == 'enable_store_pickup')) {
                    $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN enable_store_pickup VARCHAR(255) NOT NULL , "
                        . "ADD COLUMN miles_store_pickup VARCHAR(255) NOT NULL , "
                        . "ADD COLUMN match_postal_store_pickup VARCHAR(255) NOT NULL , "
                        . "ADD COLUMN checkout_desc_store_pickup VARCHAR(255) NOT NULL , "
                        . "ADD COLUMN enable_local_delivery VARCHAR(255) NOT NULL , "
                        . "ADD COLUMN miles_local_delivery VARCHAR(255) NOT NULL , "
                        . "ADD COLUMN match_postal_local_delivery VARCHAR(255) NOT NULL , "
                        . "ADD COLUMN checkout_desc_local_delivery VARCHAR(255) NOT NULL , "
                        . "ADD COLUMN fee_local_delivery VARCHAR(255) NOT NULL , "
                        . "ADD COLUMN suppress_local_delivery VARCHAR(255) NOT NULL", $warehouse_table));
                }

                $gtz_origin_markup = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'origin_markup'");
                if (!(isset($gtz_origin_markup->Field) && $gtz_origin_markup->Field == 'origin_markup')) {
                    $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN origin_markup VARCHAR(15)", $warehouse_table));
                }

                $engtz_cerasis_carrier_list = new Engtz_Cerasis_Carrier_List();
                $engtz_cerasis_carrier_list->carriers();

                $engtz_carrier_list = new gt_ltl_carriers();
                $engtz_carrier_list->gt_carriers();
            }


        }
        /**
         * Update warehouse
         */
        public static function gt_freight_update_warehouse($network_wide = null)
        {
            if ( is_multisite() && $network_wide ) {

                foreach (get_sites(['fields'=>'ids']) as $blog_id) {
                    switch_to_blog($blog_id);
                    // Origin terminal address
                    // Terminal phone number
                    global $wpdb;
                    $warehouse_table = $wpdb->prefix . "warehouse";
                    $warehouse_address = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'phone_instore'");
                    if (!(isset($warehouse_address->Field) && $warehouse_address->Field == 'phone_instore')) {
                        $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN address VARCHAR(255) NOT NULL", $warehouse_table));
                        $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN phone_instore VARCHAR(255) NOT NULL", $warehouse_table));
                    }

                    $gtz_origin_markup = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'origin_markup'");
                    if (!(isset($gtz_origin_markup->Field) && $gtz_origin_markup->Field == 'origin_markup')) {
                        $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN origin_markup VARCHAR(15)", $warehouse_table));
                    }

                    restore_current_blog();
                }

            } else {
                // Origin terminal address
                // Terminal phone number
                global $wpdb;
                $warehouse_table = $wpdb->prefix . "warehouse";
                $warehouse_address = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'phone_instore'");
                if (!(isset($warehouse_address->Field) && $warehouse_address->Field == 'phone_instore')) {
                    $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN address VARCHAR(255) NOT NULL", $warehouse_table));
                    $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN phone_instore VARCHAR(255) NOT NULL", $warehouse_table));
                }

                $gtz_origin_markup = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'origin_markup'");
                if (!(isset($gtz_origin_markup->Field) && $gtz_origin_markup->Field == 'origin_markup')) {
                    $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN origin_markup VARCHAR(15)", $warehouse_table));
                }
            }

        }

        /**
         * Create shipping rules database table
         */
        public static function create_gtz_ltl_shipping_rules_db($network_wide = null)
        {
            if ( is_multisite() && $network_wide ) {

                foreach (get_sites(['fields'=>'ids']) as $blog_id) {
                    switch_to_blog($blog_id);
                    global $wpdb;
                    $shipping_rules_table = $wpdb->prefix . "eniture_gtz_ltl_shipping_rules";

                    if ($wpdb->query("SHOW TABLES LIKE '" . $shipping_rules_table . "'") === 0) {
                        $query = 'CREATE TABLE ' . $shipping_rules_table . '(
                            id INT(10) NOT NULL AUTO_INCREMENT,
                            name VARCHAR(50) NOT NULL,
                            type VARCHAR(30) NOT NULL,
                            settings TEXT NULL,
                            is_active TINYINT(1) NOT NULL,
                            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            PRIMARY KEY (id)
                        )';

                        $wpdb->query($query);
                    }

                    restore_current_blog();
                }

            } else {
                global $wpdb;
                $shipping_rules_table = $wpdb->prefix . "eniture_gtz_ltl_shipping_rules";

                if ($wpdb->query("SHOW TABLES LIKE '" . $shipping_rules_table . "'") === 0) {
                    $query = 'CREATE TABLE ' . $shipping_rules_table . '(
                        id INT(10) NOT NULL AUTO_INCREMENT,
                        name VARCHAR(50) NOT NULL,
                        type VARCHAR(30) NOT NULL,
                        settings TEXT NULL,
                        is_active TINYINT(1) NOT NULL,
                        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (id) 
                    )';

                    $wpdb->query($query);
                }
            }
        }
    }

}