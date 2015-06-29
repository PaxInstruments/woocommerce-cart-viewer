<?php
/**
 * Plugin Name: WooCommerce Cart Viewer
 * Description: View cart session details
 * Version: 0.0.1
 * Author: Paxinstruments
 * Author URI: https://github.com/paxinstruments
 * Plugin URI: https://github.com/PaxInstruments/woocommerce-cart-viewer
 * GitHub Plugin URI: https://github.com/PaxInstruments/woocommerce-cart-viewer
 * License: GPL2
 */




//add_filter( 'cron_schedules', '' );




if( !defined('ABSPATH') ){
    exit;
}


/**
 * Check if WooCommerce is already activated.
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    class woocommerse_cart_viewer {

        /**
         * @var string
         */
        public $version = '0.0.1';

        /**
         * Constructor
         */
        function __construct() {
            register_uninstall_hook( __FILE__, 'wcv_unstall');
            register_activation_hook( __FILE__, array( __CLASS__, 'install' ) );
            $this->define_constants();
            $this->includes();
            add_action( 'init', array($this, 'init') );
        }
        
        /**
         * Fires at 'init' hook
         */
        function init() {

            $this->load_plugin_textdomain();
            $this->set_variables();
            $this->instantiate();
        }
        
        /**
         * Load locale
         */
        function load_plugin_textdomain() {

            load_plugin_textdomain( 'woocommerse-cart-viewer', false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
        }

        /**
         * Sets the variables
         */
        function __set( $name, $value ) {
            
        }

        /**
         * Define all constants
         */
        function define_constants() {

            define( 'WCV_URL', plugins_url('', __FILE__) );
            define( 'WCV_CSS', WCV_URL. "/css/" ); 
            define( 'WCV_JS',  WCV_URL. "/js/" );
            //define( 'WK_IMG',  OE_URL. "/img/" );
        }
        
        /**
         * Set necessary variables.
         */
        function set_variables() {

        }

        /**
         * Include helper classes
         */
        function includes() {
            // Includes PHP files located in 'lib' folder
            foreach( glob ( dirname(__FILE__). "/lib/*.php" ) as $lib_filename ) {
                require_once( $lib_filename );
            }
        }

        /**
         * Runs when plugin is activated.
         */
        function install() {
            $install = new wcv_install();
        }

        /**
         * Instantiate necessary classes.
         */
        function instantiate() {
            $this->admin_menu = new wcv_admin();
        }

    }

    $wkoi = new woocommerse_cart_viewer();

}
