<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Engtz_Order_From_Admin ')) 
{
    class Engtz_Order_From_Admin  
    {
        public function __construct()
        {
            add_action('wp_ajax_nopriv_engtz_cerasis_admin_order_quotes', array($this, 'engtz_cerasis_admin_order_quotes'));
            add_action('wp_ajax_engtz_cerasis_admin_order_quotes', array($this, 'engtz_cerasis_admin_order_quotes'));
        }
        
        public function engtz_cerasis_admin_order_quotes()
        {
            global $woocommerce;
            $errors = array();
            
            $order_id = ( isset($_POST['order_id']) ) ? sanitize_text_field($_POST['order_id']) : '';
            $bill_zip = ( isset($_POST['bill_zip']) ) ? sanitize_text_field($_POST['bill_zip']) : '';
            $ship_zip = ( isset($_POST['ship_zip']) ) ? sanitize_text_field($_POST['ship_zip']) : '';
            
            (strlen($ship_zip) > 0 || strlen($bill_zip) > 0) ? "" : $errors[] = "Please enter billing or shipping address.";
            
            $order = new WC_Order( $order_id );

            $items = $order->get_items();
            
            (isset($woocommerce->cart) && !empty($woocommerce->cart)) ? $woocommerce->cart->empty_cart() : "";
            
            foreach ($items as $item) 
            {
                $product_id = (isset($item['variation_id']) && !empty($item['variation_id']))?$item['variation_id'] : $item['product_id'];
                $woocommerce->cart->add_to_cart($product_id, $item['qty']);
                $cart = array('contents' => $woocommerce->cart->get_cart($product_id));

            }
            
            ((isset($cart['contents'])) && empty($cart['contents']) || (empty($items))) ? $errors[] = "Empty shipping cart content." : "";
            
            if(!empty($errors))
            {
                echo json_encode(array('errors' => $errors));
                exit();
            }
            
            $engtz_globaltranz_shipping_method = new Engtz_GlobalTranz_Shipping_Method();
            $response = $engtz_globaltranz_shipping_method->calculate_shipping($cart);

            $response = current($response);
            
            $errors[] = "No Quotes return.";
            
            echo json_encode(isset($response['cost'],$response['label']) ? array('label' => $response['label'] , 'cost' => $response['cost']) : array('errors' => $errors));
            
            exit();
        }
    }
    
    new Engtz_Order_From_Admin ();
}
    
    