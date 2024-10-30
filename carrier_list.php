<?php
/**
 * GT LTL Carriers
 *
 * @package     GT LTL Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class gt_ltl_carriers
 */
class gt_ltl_carriers
{
    /**
     * Carriers
     * @global $wpdb
     */
    function gt_carriers()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        global $wpdb;
        $table_name = $wpdb->prefix . 'gt_carriers';
        $installed_carriers = $wpdb->get_results("SELECT COUNT(*) AS carriers FROM " . $table_name);
        if ($installed_carriers[0]->carriers < 1) {
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'AACT',
                'gtz_name' => 'AAA Cooper Transportation',
                'logo' => 'aact.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'ABFS',
                'gtz_name' => 'ABF Freight System, Inc',
                'logo' => 'abfs.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'AMAP',
                'gtz_name' => 'AMA Transportation Company Inc',
                'logo' => 'amap.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'APXT',
                'gtz_name' => 'APEX XPRESS',
                'logo' => 'apxt.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'ATMR',
                'gtz_name' => 'Atlas Motor Express',
                'logo' => 'atmr.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'BCKT',
                'gtz_name' => 'Becker Trucking Inc',
                'logo' => 'bckt.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'BEAV',
                'gtz_name' => 'Beaver Express Service, LLC',
                'logo' => 'beav.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'BTVP',
                'gtz_name' => 'Best Overnite Express',
                'logo' => 'btvp.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'CAZF',
                'gtz_name' => 'Central Arizona Freight Lines',
                'logo' => 'cazf.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'CENF',
                'gtz_name' => 'Central Freight Lines, Inc',
                'logo' => 'cenf.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'CLNI',
                'gtz_name' => 'Clear Lane Freight Systems',
                'logo' => 'clni.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'CNWY',
                'gtz_name' => 'Con-Way',
                'logo' => 'cnwy.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'CPCD',
                'gtz_name' => 'Cape Cod Express',
                'logo' => 'cpcd.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'CTII',
                'gtz_name' => 'Central Transport',
                'logo' => 'ctii.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'CXRE',
                'gtz_name' => 'Cal State Express',
                'logo' => 'cxre.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'DAFG',
                'gtz_name' => 'Dayton Freight',
                'logo' => 'dafg.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'DDPP',
                'gtz_name' => 'Dedicated Delivery Professionals',
                'logo' => 'ddpp.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'DHRN',
                'gtz_name' => 'Dohrn Transfer Company',
                'logo' => 'dhrn.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'DPHE',
                'gtz_name' => 'Dependable Highway Express',
                'logo' => 'dphe.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'DTST',
                'gtz_name' => 'DATS Trucking Inc',
                'logo' => 'dtst.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'DUBL',
                'gtz_name' => 'Dugan Truck Lines',
                'logo' => 'dubl.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'DYLT',
                'gtz_name' => 'Daylight Transport',
                'logo' => 'dylt.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'EXLA',
                'gtz_name' => 'Estes Express Lines',
                'logo' => 'exla.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'FXFE',
                'gtz_name' => 'FedEx Freight Priority',
                'logo' => 'fedex.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'FXNL',
                'gtz_name' => 'FedEx Freight Economy',
                'logo' => 'fedex.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'FCSY',
                'gtz_name' => 'Frontline Freight Inc',
                'logo' => 'fcsy.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'FLAN',
                'gtz_name' => 'Flo Trans',
                'logo' => 'flan.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'FTSC',
                'gtz_name' => 'Fort Transportation',
                'logo' => 'ftsc.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'FWDN',
                'gtz_name' => 'Forward Air, Inc',
                'logo' => 'fwdn.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'GLDF',
                'gtz_name' => 'Gold Coast Freightways',
                'logo' => 'gldf.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'HMES',
                'gtz_name' => 'Holland',
                'logo' => 'hmes.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'LAXV',
                'gtz_name' => 'Land Air Express Of New England',
                'logo' => 'laxv.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'LKVL',
                'gtz_name' => 'Lakeville Motor Express Inc',
                'logo' => 'lkvl.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'MIDW',
                'gtz_name' => 'Midwest Motor Express',
                'logo' => 'midw.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'NEBT',
                'gtz_name' => 'Nebraska Transport',
                'logo' => 'nebt.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'NEMF',
                'gtz_name' => 'New England Motor Freight',
                'logo' => 'nemf.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'NOPK',
                'gtz_name' => 'North Park Transportation Co',
                'logo' => 'nopk.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'NPME',
                'gtz_name' => 'New Penn Motor Express',
                'logo' => 'npme.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'OAKH',
                'gtz_name' => 'Oak Harbor Freight Lines',
                'logo' => 'oakh.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'ODFL',
                'gtz_name' => 'Old Dominion',
                'logo' => 'odfl.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'PITD',
                'gtz_name' => 'Pitt Ohio Express, LLC',
                'logo' => 'pitd.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'PMLI',
                'gtz_name' => 'Pace Motor Lines, Inc',
                'logo' => 'pmli.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'PNII',
                'gtz_name' => 'ProTrans International',
                'logo' => 'pnii.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'PYLE',
                'gtz_name' => 'A Duie PYLE',
                'logo' => 'pyle.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'RDFS',
                'gtz_name' => 'Roadrunner Transportation Services',
                'logo' => 'rdfs.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'RDWY',
                'gtz_name' => 'YRC',
                'logo' => 'rdwy.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'RETL',
                'gtz_name' => 'USF Reddaway',
                'logo' => 'retl.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'RJWI',
                'gtz_name' => 'RJW Transport',
                'logo' => 'rjwi.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'RLCA',
                'gtz_name' => 'R & L Carriers Inc',
                'logo' => 'rlca.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'ROSI',
                'gtz_name' => 'Roseville Motor Express',
                'logo' => 'rosi.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'RXIC',
                'gtz_name' => 'Ross Express',
                'logo' => 'rxic.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'SAIA',
                'gtz_name' => 'SAIA',
                'logo' => 'saia.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'SEFL',
                'gtz_name' => 'Southeastern Freight Lines',
                'logo' => 'sefl.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'SHIF',
                'gtz_name' => 'Shift Freight',
                'logo' => 'shif.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'SMTL',
                'gtz_name' => 'Southwestern Motor Transport',
                'logo' => 'smtl.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'STDF',
                'gtz_name' => 'Standard Forwarding Company Inc',
                'logo' => 'stdf.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'SVSE',
                'gtz_name' => 'SuperVan Service Co. Inc',
                'logo' => 'svse.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'UPGF',
                'gtz_name' => 'UPS Freight',
                'logo' => 'upgf.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'WARD',
                'gtz_name' => 'Ward Trucking',
                'logo' => 'ward.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'WEBE',
                'gtz_name' => 'West Bend Transit',
                'logo' => 'webe.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'CGOJ',
                'gtz_name' => 'Cargomatic ',
                'logo' => 'Cargomatic.png',
                'carrier_status' => '1'
            ));

            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'WTVA',
                'gtz_name' => 'Wilson Trucking Corporation',
                'logo' => 'wtva.png',
                'carrier_status' => '1'
            ));

            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'AVRT',
                'gtz_name' => 'Averitt Express, Inc',
                'logo' => 'averitt.png',
                'carrier_status' => '1'
            ));
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'BRTC',
                'gtz_name' => 'BC Freightways',
                'logo' => 'brtc.png',
                'carrier_status' => '1'
            ));
        }

        // FedEx Priority
        $query = $wpdb->get_results("SELECT * FROM $table_name WHERE gtz_scac = 'FXFE'");
        if (empty($query)) {
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'FXFE',
                'gtz_name' => 'FedEx Freight Priority',
                'logo' => 'fedex.png',
                'carrier_status' => '1'
            ));
        }

        // FedEx Economy
        $query = $wpdb->get_results("SELECT * FROM $table_name WHERE gtz_scac = 'FXNL'");
        if (empty($query)) {
            $wpdb->insert(
                $table_name, array(
                'gtz_scac' => 'FXNL',
                'gtz_name' => 'FedEx Freight Economy',
                'logo' => 'fedex.png',
                'carrier_status' => '1'
            ));
        }
    }

}
new gt_ltl_carriers();