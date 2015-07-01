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
        add_action( 'admin_enqueue_scripts', array($this, 'load_css') );
        add_action( 'admin_menu', array( &$this, 'admin_menu') );
        add_action('woocommerce_cart_updated', array(&$this, 'woocommerce_wcv_cart_updated'));
        //woocommerce_add_to_cart| $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data );
     }

     public function admin_menu()
     {
        if ( current_user_can( 'manage_woocommerce' ) ) {
            add_submenu_page( 'woocommerce', __( 'Cart Viewer', 'woocommerce-cart-viewer' ),  __( 'Cart Viewer', 'woocommerce-cart-viewer' ) , 'manage_woocommerce', 'wcv-view', array( $this, 'wcv_view' ) );
        }
     }

     public function load_css()
     {
        wp_enqueue_style( 'wcv_style', WCV_CSS.'style.css' );
     }

     public function wcv_view_session($session)
     {
        global $wpdb;
        $url =  $_SERVER['REQUEST_URI'];
        $table_name = $wpdb->base_prefix . "cv_history";
        $query = "SELECT * from $table_name WHERE id = %s;";
        $results = $wpdb->get_row( $wpdb->prepare($query, $session), ARRAY_A );
        $back = remove_query_arg('viewsession', $url);
        print "<a href='$back'>Go back to session view</a>";
        print '<h1>Viewing Session</h1>';
        if(!empty($results)){
            $data = json_decode($results['data'], true);
            foreach ($data['items'] as $item) {
                $product = wc_get_product($item['product_id']);
                $pimg = $product->get_image();
                $title = $product->get_title();
                $quantity = $item['quantity'];
                //print_r($item);
                print "<div class='wcv_info'>
                            $pimg
                            <div class='wcv_info_data'>
                            <span>$title</span><br>
                            <span>$quantity items</span><br>
                            <span>variation?</span>
                            </div>
                        </div>
                        ";
                //print '<hr>';
            }
        }
        
     }

     public function pagnation($page, $size, $num_per_page)
     {
        $url =  $_SERVER['REQUEST_URI'];

        $limit = '';
        $pagnation = '';

        try {
            $page = intval($page);
            $limit = sprintf('LIMIT %d, %d', $page * $num_per_page, $num_per_page );
            $pages = ($size/$num_per_page) % 10;

            $previous = add_query_arg( array('p'=>$page-1), $url );
            if( $page>0 ){
                $pagnation = "<a href='$previous'>&lt; previous</a>";
            } else {
                $pagnation = "&lt; previous";
            }
            for ($i=0; $i < $pages+1; $i++) { 
                $lpage = add_query_arg( array('p'=>$i), $url );
                $vp = $i+1;
                if ( $i != $page ) {
                    $pagnation .= " | <a href='$lpage'>$vp</a> ";
                } else {
                    $pagnation .= " | $vp ";
                }
            }
            $next = add_query_arg( array('p'=>$page+1), $url );
            if ( $page < $pages ) {
                $pagnation .= "| <a href='$next'>next &gt;</a>";
            } else {
                $pagnation .= "| next &gt;";
            }
        } catch (Exception $e) {
            
        }
        return array($pagnation, $limit);
     }

     public function wcv_view()
     {
        global $wpdb;
        if(isset($_GET['viewsession'])){
            $this->wcv_view_session( $_GET['viewsession'] );
            return;
        }
        $table_name = $wpdb->base_prefix . "cv_history";
        $limit = '';

        
        $pagnation = '';
        $num_per_page = 10;
        $get_size = "SELECT count(*) from $table_name;";
        $size = $wpdb->get_var( $get_size );

        $p = (isset($_GET['p'])) ? $_GET['p'] : '0';
        $pag = $this->pagnation($p, $size, $num_per_page);
        $pagnation = $pag[0];
        $limit = $pag[1];

        $query = "SELECT * from $table_name $limit;";
        $results = $wpdb->get_results( $query, ARRAY_A );

        $filtered = array();

        $url =  $_SERVER['REQUEST_URI'];

        foreach ($results as $row) {

            $nrow = array();

            $data = json_decode($row['data'], true);
            $citems = 0;# count($data['items']);
            foreach ($data['items'] as $item) {
                $citems += $item['quantity'];
            }

            $nrow['Session'] = $data['cookie'];
            $nurl = add_query_arg( array('viewsession'=>$row['id']), $url );
            $nrow['Data'] = "<a href='$nurl'>$citems items in cart by user {$data['server']['REMOTE_ADDR']}";
            $nrow['Time'] = date('Y/m/d h:i:s', strtotime($row['time']));

            $filtered[] = $nrow;
        }

        print "<h1>Cart Sessions</h1>";
        print "$pagnation<br>";
        $this->html_show_array($filtered);
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
        
        $session = WC()->session->get_session_cookie(); //$customer_id, $session_expiration, $session_expiring, $cookie_hash 
        $user_id = get_current_user_id();
        
        $useful = array(
                'session' => COOKIEHASH,
                'cookie' => $session[3], // $cookie_hash 
                'server' => array( 
                    'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'], 
                    'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ),
                'user_id' => $user_id,
                'items' => $items
            );
         
        
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