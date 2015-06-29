<?php

/**
 * Install tables
 */


/**
* 
*/
class wcv_install
{
    
    function __construct()
    {
        global $wpdb;

        // history table
        $table_name = $wpdb->base_prefix . "cv_history";
        $wp_cv_history = "CREATE TABLE $table_name ( `id` INT NOT NULL AUTO_INCREMENT , `data` TEXT NOT NULL , `time` DATETIME NOT NULL , PRIMARY KEY (`id`));";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $wp_cv_history );
    }
}
 