<?php
    /*
     * Plugin Name: Socialhero Promotions
     * Description: Allow your customers to shop while helping a good cause
     * Version: 1.0.0
     * Author: Proficient
     */
    defined( 'ABSPATH' ) || exit; 
    define( 'SOCIALPROMOTION_PLUGIN_DIR', dirname(__FILE__).'/' ); 
    //include_once WP_SOCIALPROMOTION_PLUGIN_DIR .'/woocommerce/woocommerce.php';
    class SocialHero_AdminNotice {
        protected $min_wc = '5.0.0'; //replace '5.0.0' with your dependent plugin version number
        /**
         * Register the activation hook
         */
        public function __construct() {
            register_activation_hook( __FILE__, array( $this, 'socialhero_install' ) );
        }
        /**
         * Check the dependent plugin version
         */
        protected function socialhero_is_wc_compatible() {          
            return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, $this->min_wc, '>=' );
        }
        /**
         * Function to deactivate the plugin
         */
        protected function socialhero_deactivate_plugin() {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            if ( isset( $_GET['activate'] ) ) {
                unset( $_GET['activate'] );
            }
        }
        /**
         * Deactivate the plugin and display a notice if the dependent plugin is not compatible or not active.
         */
        public function socialhero_install() {
            if ( ! $this->socialhero_is_wc_compatible() || ! class_exists( 'WooCommerce' ) ) {
                $this->socialhero_deactivate_plugin();
                wp_die( 'Could not be activated. ' . $this->get_socialhero_admin_notices() );
            } else {
                //do your fancy staff here
                
                global $wpdb;
               
                $charset_collate = $wpdb->get_charset_collate();
                $discount_code = $wpdb->prefix . 'discount_code';
                $sql = "CREATE TABLE `$discount_code` 
                (`id` int(11) NOT NULL AUTO_INCREMENT,
                `discount_price` varchar(100) DEFAULT NULL,
                `apiToken` varchar(100) DEFAULT NULL,
                `client_number` varchar(100) DEFAULT NULL,
                `code` varchar(100) DEFAULT NULL,
                `discount_status` varchar(100) DEFAULT NULL,
                `order_id` varchar(100) DEFAULT NULL,
                PRIMARY KEY(id)
                ) ENGINE=Innodb DEFAULT CHARSET=latin1;
                ";
                if ($wpdb->get_var("SHOW TABLES LIKE '$discount_code'") != $discount_code) {
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
                }
            }
        }
        /**
         * Writing the admin notice
         */
        protected function get_socialhero_admin_notices() {
            return sprintf(
                '%1$s requires WooCommerce version %2$s or higher installed and active. You can download WooCommerce latest version %3$s OR go back to %4$s.',
                '<strong></strong>',
                $this->min_wc,
                '<strong><a href="https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip">from here</a></strong>',
                '<strong><a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">plugins page</a></strong>'
            );
        }
    }
    new SocialHero_AdminNotice();
    
    //include backend functions
    include( SOCIALPROMOTION_PLUGIN_DIR . 'includes/backend/functions.php');
    
    //include frontend functions
    include( SOCIALPROMOTION_PLUGIN_DIR . 'includes/frontend/functions.php'); 
    
    //add settings link
    add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'socialhero_settings_link');
     function socialhero_settings_link( $links ) {
        $url = admin_url()."admin.php?page=socialhero-settings";
        $settings_link = '<a href="'.esc_url( $url ).'">'.esc_html( 'Settings' ).'</a>';
        $links[] = $settings_link;
        return $links;
    }   
    
    register_deactivation_hook( __FILE__, 'socialhero_remove_discount_table' );
    function socialhero_remove_discount_table()
    {
        if(esc_attr( get_option('sh-agree'))== 'true'){
        global $wpdb;
        $discount_code = $wpdb->prefix . 'discount_code'; 
        $sql = "DROP TABLE IF EXISTS $discount_code";
        $res=$wpdb->query($sql);
        }
    }
    
    function check_woocommerce_activate() {
        if( !function_exists( 'is_plugin_inactive' ) ) :
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        endif;
        //COMMON WOOCOMMERCE METHOD
        if( !class_exists( 'WooCommerce' ) ) :
            
        //if( is_plugin_inactive( 'woocommerce/woocommerce.php' ) ) :
            add_action( 'admin_init', 'socialhero_plugin_deactivate' );
            add_action( 'admin_notices', 'socialhero_admin_notice' );
            
            function socialhero_plugin_deactivate() {
                deactivate_plugins( plugin_basename( __FILE__ ) );
            }
            function socialhero_admin_notice() {
                echo '<div class="error"><p><strong>WooCommerce</strong> must be installed and activated to use <b>Socialhero Promotions</b> plugin.</p></div>';
                if( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
            }
        endif;
    }
    add_action( 'plugins_loaded', 'check_woocommerce_activate' );    
    