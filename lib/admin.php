<?php

/*
define 4 tabs
Cart Sessions
 -> cart view
Email
 -> configure customer email template and schedule

*/




 /**
 * 
 */
 class wcv_admin
 {
     
     function __construct()
     {
         add_action( 'admin_menu', array($this, 'admin_menu') );
         add_action('woocommerce_cart_updated', array(&$this, 'woocommerce_wcv_cart_updated'));
         //woocommerce_add_to_cart| $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data );
     }

     public function admin_menu()
     {
        if ( current_user_can( 'manage_woocommerce' ) ) {
            add_submenu_page( 'woocommerce', __( 'Cart Viewer', 'woocommerce-cart-viewer' ),  __( 'Cart Viewer', 'woocommerce-cart-viewer' ) , 'manage_woocommerce', 'wcv-view', array( $this, 'wcv_view' ) );
        }
     }

     public function wcv_view()
     {
        global $wpdb;

        $table_name = $wpdb->base_prefix . "cv_history";
        $query = "SELECT * from $table_name;";
        $results = $wpdb->get_results( $query, ARRAY_A );

        $filtered = array();

        foreach ($results as $row) {

            $nrow = array();

            $data = json_decode($row['data'], true);
            $citems = 0;# count($data['items']);
            foreach ($data['items'] as $item) {
                $citems += $item['quantity'];
            }

            $nrow['Session'] = $data['cookie'];
            $nrow['Data'] = "$citems items in cart by user {$data['server']['REMOTE_ADDR']}";
            $nrow['Time'] = date('Y/m/d h:i:s', strtotime($row['time']));

            $filtered[] = $nrow;
        }

        // if(count($results) > 0){
        print "<h1>Cart Sessions</h1>";
        $this->html_show_array($filtered);
        // }
        //print '<pre>'; 
        //print $results[];
        //print_r($results);
     }

     public function woocommerce_wcv_cart_updated()
     {
        global $wpdb;
        $cart = new WC_Cart();
        $cart->get_cart_from_session();
        $data = $cart->get_cart();
        $items = array();
        foreach ($data as $key => $value) {
            ////variation_id, product_id, quantity, 
            $items[] = array(
                'product_id' => $value['product_id'],
                'variation_id' => $value['variation_id'],
                'quantity' => $value['quantity']
                );
        }
        if(empty($data) or ! defined('COOKIEHASH')) return;
        //$customer_id, $session_expiration, $session_expiring, $cookie_hash 
        $session = WC()->session->get_session_cookie(); #$cart->has_session();
        $user_id = get_current_user_id();
        
        $useful = array(
                'session' => COOKIEHASH,
                'cookie' => $session[3],
                'server' => array( 
                    'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'], 
                    'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ),
                'user_id' => $user_id,
                'items' => $items
            );
        //variation_id, product_id, quantity, 
        
        $jdata=json_encode($useful);
        $current_time = current_time('timestamp');

        $table_name = $wpdb->base_prefix . "cv_history";
        
        $has_cart = "SELECT id FROM $table_name WHERE `data` like '%".$useful['cookie']."%' ";
        $cart_id = $wpdb->get_var($has_cart);

        if(isset($cart_id) and $cart_id >= 0 ){
            $query = "UPDATE $table_name set data = %s where `id` = %d ";
            $wpdb->query( $wpdb->prepare(
                    $query, $jdata, $cart_id
                )
            );
        } else {
            $query = "INSERT INTO $table_name values (NULL, %s, NOW()) ";
             
            $wpdb->query( $wpdb->prepare(
                    $query, $jdata
                )
            );
        }

        
     }

    function html_show_array($table) {
        //wp-list-table widefat fixed striped posts
        echo "<table class='wp-list-table widefat fixed striped posts'>";
        
        echo "<tr>";
        foreach (array_keys($table[0]) as $key) {
            echo "<th>" . $key . "</th>";
        }
        echo "</tr>";
        
        foreach ($table as $rows => $row) {
            echo "<tr>";
            foreach ($row as $col => $cell) {
                echo "<td>" . $cell . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
 }