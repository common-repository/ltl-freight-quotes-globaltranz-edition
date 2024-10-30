<?php

/**
 * Third party rates used in eniture shipping services.
 */

namespace EnFreightviewOtherRates;

/**
 * Filter other rates will be shown on the cart|checkout page.
 * Class EnFreightviewOtherRates
 * @package EnFreightviewOtherRates
 */
if (!class_exists('EnFreightviewOtherRates')) {

    class EnFreightviewOtherRates
    {
        /**
         * @param array $instor_pickup_local_delivery
         * @param string $en_is_shipment
         * @param array $en_origin_address
         * @param array $api_rates
         * @param array $en_settings
         * @return array
         */
        static public function en_extra_custom_services($instor_pickup_local_delivery, $en_is_shipment, $en_origin_address, $api_rates, $en_settings)
        {
            $rates = [];
            if (!empty($instor_pickup_local_delivery) && $en_is_shipment === 'en_single_shipment') {
                $phone_instore = $address = $city = $state = $zip = $phone_instore = $checkout_desc_store_pickup = $checkout_desc_local_delivery = $suppress_local_delivery = '';
                $fee_local_delivery = 0;
                extract($en_origin_address);

                if (isset($instor_pickup_local_delivery['inStorePickup']['status']) &&
                    $instor_pickup_local_delivery['inStorePickup']['status'] === '1') {
                    $label = strlen($checkout_desc_store_pickup) > 0 ? $checkout_desc_store_pickup : 'In-store pick up';
                    // Origin terminal address
                    $total_distance = isset($instor_pickup_local_delivery['totalDistance']) ? $instor_pickup_local_delivery['totalDistance'] : '';
                    strlen($total_distance) > 0 ? $label .= ', Free | ' . str_replace("mi", "miles", $total_distance) . ' away' : '';
                    strlen($address) > 0 ? $label .= ' | ' . $address : '';
                    strlen($city) > 0 ? $label .= ', ' . $city : '';
                    strlen($state) > 0 ? $label .= ' ' . $state : '';
                    strlen($zip) > 0 ? $label .= ' ' . $zip : '';
                    strlen($phone_instore) > 0 ? $label .= ' | ' . $phone_instore : '';
                    $rates[] = array(
                        'id' => 'in-store-pick-up',
                        'cost' => 0,
                        'label' => $label,
                        'plugin_name' => 'globalTranz',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture'
                    );
                }

                if (isset($instor_pickup_local_delivery['localDelivery']['status']) &&
                    $instor_pickup_local_delivery['localDelivery']['status'] === '1') {
                    $label = strlen($checkout_desc_local_delivery) > 0 ? $checkout_desc_local_delivery : 'Local delivery';
                    $rates[] = array(
                        'id' => 'local-delivery',
                        'cost' => $fee_local_delivery > 0 ? $fee_local_delivery : 0,
                        'label' => $label,
                        'plugin_name' => 'globalTranz',
                        'plugin_type' => 'ltl',
                        'owned_by' => 'eniture'
                    );
                }

                if (($suppress_local_delivery == 'on' || $suppress_local_delivery == 'yes' || $suppress_local_delivery === '1') && !empty($rates)) {
                    $api_rates = [];
                }
            }

            if ($en_settings['own_freight'] === 'yes') {
                $own_freight_label = (strlen($en_settings['own_freight_label']) > 0) ?
                    $en_settings['own_freight_label'] : "I'll arrange my own freight";

                $rates[] = [
                    'id' => 'engtz_cerasis_shipping_method:' . 'en_own_freight',
                    'cost' => 0,
                    'label' => $own_freight_label,
                    'plugin_name' => 'globalTranz',
                    'plugin_type' => 'ltl',
                    'owned_by' => 'eniture'
                ];
            }

            $api_rates = array_merge($rates, $api_rates);

            return $api_rates;
        }

    }

}
