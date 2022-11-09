<?php

    // create custom plugin settings menu
    add_action('admin_menu', 'my_socialhero_create_menu');
    function my_socialhero_create_menu() {
     //create new top-level menu
         add_menu_page('Socialhero Setting', 'Socialhero Setting', 'administrator', 'socialhero-settings', 'my_socialhero_settings_page');
         //call register settings function
         add_action('admin_init', 'socialhero_settings_page');
    }
    function socialhero_settings_page() {
         //register our settings
        register_setting( 'socialhero-settings-group', 'sh-branch_id');
        register_setting( 'socialhero-settings-group', 'sh-origin');
        register_setting( 'socialhero-settings-group', 'sh-mode');  
        register_setting( 'socialhero-settings-group', 'sh-agree');
    }    
    function my_socialhero_settings_page(){
       include( SOCIALPROMOTION_PLUGIN_DIR . 'includes/backend/index.php');
    }
    add_action( 'woocommerce_update_product', 'socialhero_on_product_save', 10, 1 );
    function socialhero_on_product_save($product_id) 
    {
       $url_product = site_url().'/wp-json/wp/v2/product/'.$product_id;
       $apiUrl = 'https://api.socialhero.com.mx/APIComercios/public/oauth/product/productActualization';
       $resp = wp_remote_get(esc_url($url_product));
       $body = wp_remote_retrieve_body( $resp );
       $result = json_decode( $body );
       
       if($result){
            $spResponse = wp_remote_post(esc_url_raw($apiUrl), array(
                'method' => 'POST',
                'timeout' => 45,
                'blocking' => true,
                'headers' => array(
                    'Content-Type: application/json',
                 ),
                'body' => esc_html($result)
                )
            );
        }
    }    
    
?>