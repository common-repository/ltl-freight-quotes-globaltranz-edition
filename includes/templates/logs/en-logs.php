<script type="text/javascript">
    jQuery(function() {
        jQuery('a').on('click', function(e) {
            const class_name = this.className;
            const show_class_name = class_name.includes('show') ? class_name.replace('show', 'hide') : class_name.replace('hide', 'show');

            if (class_name.includes('show') || class_name.includes('hide')) {
                e.preventDefault();
            }

            jQuery('.' + class_name).hide();
            jQuery('.' + show_class_name).show();
        })
    });
</script>

<?php

if (!class_exists('EnGTZLfqLogs')) {
    class EnGTZLfqLogs
    {
        public function __construct()
        {
            $this->enLogs();
        }

        // Logs request
        public function enLogs()
        {
            $api_enabled = get_option('cerasis_global_tranz_api_endpoint');
            $carrier_name = $api_enabled == 'wc_cerasis_api_fields' ? 'cerasis' : ($api_enabled == 'wc_global_tranz_new_api_fields' ? 'GlobalTranz New API' : 'globalTranz');
            $data = array(
                'serverName' => engtz_cerasis_get_domain(),
                'licenseKey' => get_option('wc_settings_cerasis_licence_key'),
                'lastLogs' => '25',
                'carrierName' => $carrier_name,
            );

            require_once 'en-json-tree-view/en-jtv.php';

            $url = GT_HITTING_DOMAIN_URL . '/request-log/index.php';
            $obj_classs = new Engtz_Curl_Class();
            $logs = $obj_classs->get_curl_response($url, $data);
            $logs = (isset($logs) && is_string($logs) && strlen($logs) > 0) ? json_decode($logs, true) : [];

            echo '<table class="en_logs">';

            if (isset($logs['severity'], $logs['data']) && $logs['severity'] == 'SUCCESS') {
                echo '<tr>';
                echo '<th>Log ID</th>';
                echo '<th>Request Time</th>';
                echo '<th>Response Time</th>';
                echo '<th>Latency</th>';
                echo '<th>Items</th>';
                echo '<th>DIMs (L x W x H)</th>';
                echo '<th>Qty</th>';
                echo '<th>Sender Address</th>';
                echo '<th>Receiver Address</th>';
                echo '<th>Response</th>';
                echo '</tr>';

                foreach ($logs['data'] as $key => $shipment) {
                    echo '<tr>';
                    if (empty($shipment) || !is_array($shipment)) continue;

                    $request = $response = $carrier = $status = '';
                    extract($shipment);
                    $request = is_string($request) && strlen($request) > 0 ? json_decode($request, true) : [];
                    if (empty($request) || !is_array($request)) continue;

                    $receiverLineAddress = $receiverCity = $receiverState = $receiverZip = '';
                    extract($request);
                    $en_fdo_meta_data = (isset($request['en_fdo_meta_data'])) ? $request['en_fdo_meta_data'] : [];
                    $commdityDetails = isset($commdityDetails) ? $commdityDetails : [];
                    $items = $address = [];
                    extract($en_fdo_meta_data);
                    $en_address = $address;
                    $en_qty = $en_items = $en_dim = '';        
                    $class_name = 'log-gtz' . $key . rand(1, 100);

                    if (isset($carrier_mode) && ($carrier_mode == 'test' || $carrier_mode == 'getcarriers')) {
                        continue;
                    }
                    
                    // format log data for New API log detail
                    if (isset($request['carrierName']) && strtolower($request['carrierName']) == 'wwe ltl') {
                        $dims_arr = isset($request['product_length_array']) ? $request['product_length_array'] : [];

                        foreach ($dims_arr as $key => $value) {
                            $commdityDetails[$key] = [
                                'piecesOfLineItem' => $request['speed_freight_post_quantity_array'][$key],
                                'lineItemDescription' => $request['speed_freight_post_title_array'][$key],
                                'lineItemLength' => $request['product_length_array'][$key],
                                'lineItemWidth' => $request['product_width_array'][$key],
                                'lineItemHeight' => $request['product_height_array'][$key],
                                'lineItemWeight' => $request['speed_freight_product_weight'][$key],
                            ];
                        }

                        // sender address
                        $senderCity = $request['speed_freight_senderCity'];
                        $senderState = $request['speed_freight_senderState'];
                        $senderZip = $request['speed_freight_senderZip'];
                        $senderCountryCode = $request['speed_freight_senderCountryCode'];

                        // receiver address
                        $receiverCity = $request['freight_reciver_city'];
                        $receiverState = $request['freight_receiver_state'];
                        $receiverZip = $request['freight_receiver_zip_code'];
                        $receiverCountryCode = $request['receiverCountryCode'];
                    }

                    $items = $commdityDetails;
                                      
                    foreach ($items as $key => $item) {
                        $lineItemDescription = '';
                        extract($item);
                        
                        $en_qty .= strlen($en_qty) > 0 ? "<br> $piecesOfLineItem" : $piecesOfLineItem;
                        $en_items .= strlen($en_items) > 0 ? "<br> $lineItemDescription" : $lineItemDescription;
                        $en_dim .= strlen($en_dim) > 0 ? "<br> $lineItemLength X $lineItemWidth X $lineItemHeight" : "$lineItemLength X $lineItemWidth X $lineItemHeight";
                    }

                    $en_updated_qty = $en_updated_items = $en_updated_dim = '';
                    $updated_items = count($items) > 5 ? array_slice($items, 0, 5) : $items;
                    if (!empty($updated_items)) {
                        foreach ($updated_items as $key => $item) {
                            $lineItemDescription = '';
                            extract($item);

                            $en_updated_qty .= strlen($en_updated_qty) > 0 ? "<br> $piecesOfLineItem" : $piecesOfLineItem;
                            $en_updated_items .= strlen($en_updated_items) > 0 ? "<br> $lineItemDescription" : $lineItemDescription;
                            $en_updated_dim .= strlen($en_dim) > 0 ? "<br> $lineItemLength X $lineItemWidth X $lineItemHeight" : "$lineItemLength X $lineItemWidth X $lineItemHeight";
                        }    
                    }
                    
                    // Sender address
                    $address = '';
                    extract($en_address);
                    $en_sender = strlen(trim($address) > 0) ? "$address, " : '';
                    $senderCountryCode = str_replace('USA', 'US', $senderCountryCode);
                    $en_sender .= "$senderCity, $senderState $senderZip $senderCountryCode";

                    // Receiver address
                    $en_receiver = strlen(trim($receiverLineAddress) > 0) ? "$receiverLineAddress, " : '';
                    $receiverCountryCode = str_replace('USA', 'US', $receiverCountryCode);
                    $en_receiver .= "$receiverCity, $receiverState $receiverZip $receiverCountryCode";
                    $carrier = ucfirst($carrier);
                    $status = ucfirst($status);
                    $request_time = $this->setTimeZone($request_time);
                    $response_time = $this->setTimeZone($response_time);
                    $latency = strtotime($response_time) - strtotime($request_time);
                    $response = str_replace(array("\r", "\n", "\t"), '', $response);
                    $response = str_replace('96" ', '96 ', $response);
                    $response = str_replace('75"=96" ', '75=96 ', $response);
                    $response = str_replace('75"=96 ', '75=96 ', $response);

                    echo "<td>$id</td>";
                    echo "<td>$request_time</td>";
                    echo "<td>$response_time</td>";
                    echo "<td>$latency</td>";
                    
                    $name = 'show-' . $class_name;
                    if (count($items) > 5) {
                        echo "<td class='items_space $name'>$en_updated_items <br /> <a href='#' class='$name'>Show more items</a> </td>";
                    } else {
                        echo "<td class='items_space $name'>$en_updated_items</td>";
                    }
                    
                    echo "<td class='dims_space $name'>$en_updated_dim</td>";
                    echo "<td class='$name'>$en_updated_qty</td>";
                    
                    $name = 'hide-' . $class_name;
                    echo "<td class='items_space hide $name'>$en_items <br /> <a href='#' class='$name'>Hide more items</a> </td>";
                    echo "<td class='dims_space hide $name'>$en_dim</td>";
                    echo "<td class='hide $name'>$en_qty</td>";

                    echo "<td>$en_sender</td>";
                    echo "<td>$en_receiver</td>";
                    echo '<td><a href = "#en_jtv_showing_res" class="response" onclick=\'en_jtv_res_detail(' . $response . ')\'>' . $status . '</a></td>';
                    echo '</tr>';
                }
            } else {
                echo '<div class="user_guide_fdo">';
                echo '<p>Logs are not available.</p>';
                echo '</div>';
            }

            echo '<table>';
        }

        public function setTimeZone($date_time)
        {
            $time_zone = wp_timezone_string();
            if (empty($time_zone)) {
                return $date_time;
            }

            $converted_date_time = new DateTime($date_time, new DateTimeZone($time_zone));

            return $converted_date_time->format('m/d/Y h:i:s');
        }
    }

    new EnGTZLfqLogs();
}
