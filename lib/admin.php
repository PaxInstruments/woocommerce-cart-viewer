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
        $results = $wpdb->get_results( $query );
        if(count($results) > 0){
            html_show_array($results);
        }
     }

    function html_show_array($table) {
        echo "<table class='wp-list-table widefat fixed posts' border='1'>";
        
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