<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Engtz_Cerasis_Quotes')) {
    class Engtz_Cerasis_Quotes
    {
        /**
         * rating method from quote settings
         * @var string type
         */
        public $rating_method;

        /**
         * rates from web service
         * @var array type
         */
        public $quotes;

        /**
         * wwe settings
         * @var array type
         */
        public $quote_settings;

        /**
         * label from quote settings
         * @var atring type
         */
        public $wwe_label;

        /**
         * class name
         * @var class type
         */
        public $VersionCompat;
        public $count = 1;
        public $total_carriers;
        function __construct()
        {
//                construct
        }

        /**
         * set values in class attributes and return quotes
         * @param array type $quotes
         * @param array type $quote_settings
         * @return array type
         */
        public function calculate_quotes($quotes, $quote_settings)
        {
            $this->quotes = $quotes;
            $this->quote_settings = $quote_settings;
            $this->total_carriers = $this->quote_settings['total_carriers'];
            $this->VersionCompat = new Engtz_VersionCompat();
            $rating_method = $this->quote_settings['rating_method'];

            $cerasis_global_tranz_shipping_service = get_option('cerasis_global_tranz_shipping_service');
            if ($cerasis_global_tranz_shipping_service == 'wc_final_mile_service') {
                return $quotes;
            }

            return $this->$rating_method();
        }

        function rand_string()
        {
            return md5(uniqid(mt_rand(), true));
        }

        /**
         * calculate average for quotes
         * @return array type
         */
        public function average_rate()
        {
            $rate_sum = 0;
            $this->quotes = (isset($this->quotes) && (is_array($this->quotes))) ? array_slice($this->quotes, 0, $this->total_carriers) : [];
            $rate_list = $this->VersionCompat->engtz_array_column($this->quotes, 'cost');
            if (count($this->quotes) != 0) {
                $rate_sum = array_sum($rate_list) / count($this->quotes);
            }
            $quotes_reset = reset($this->quotes);
            $this->count++;

            // Updates cost in meta data for order widget
            $meta_data = isset($quotes_reset['meta_data']) ? $quotes_reset['meta_data'] : [];
            if (!empty($meta_data) && isset($meta_data['en_fdo_meta_data']['rate'])) {
                $meta_data['en_fdo_meta_data']['rate']['cost'] = $rate_sum;
            }

            $rate[] = array(
                'id' => 'av_rate'.$this->count,
                'cost' => $rate_sum,
                'markup' => (isset($quotes_reset['markup'])) ? $quotes_reset['markup'] : "",
                'label_sufex' => (isset($quotes_reset['label_sufex'])) ? $quotes_reset['label_sufex'] : array(),
                'append_label' => (isset($quotes_reset['append_label'])) ? $quotes_reset['append_label'] : "",
                'meta_data' => $meta_data,
            );
            return $rate;
        }

        /**
         * calculate cheapest rate
         * @return type
         */
        public function Cheapest()
        {
            return (isset($this->quotes) && (is_array($this->quotes))) ? array_slice($this->quotes, 0, 1) : array();
        }

        /**
         * calculate cheapest rate numbers
         * @return array type
         */
        public function cheapest_options()
        {
            return (isset($this->quotes) && (is_array($this->quotes))) ? array_slice($this->quotes, 0, $this->total_carriers) : array();
        }

    }
}

