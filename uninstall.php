<?php

function wcv_unstall()
{
    $wp_cv_history = $wpdb->base_prefix . "cv_history";
    $sql_ac_abandoned_cart_history = "DROP TABLE " . $wp_cv_history;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $wpdb->get_results($wp_cv_history);
}
