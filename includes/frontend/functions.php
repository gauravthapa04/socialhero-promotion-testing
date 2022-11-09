<?php
    add_action( 'init', 'socialhero_script_enqueuer' );
    function socialhero_script_enqueuer() {
       wp_localize_script( 'social_hero_script', 'socialheroAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
       wp_enqueue_script( 'jquery' );
       wp_enqueue_script( 'social_hero_script' );
    }
    // function for show input field in the cart page
    function prefix_after_cart_item_name() {
     //get cart form
      if(esc_attr( get_option('sh-branch_id')) != '')
      {
       include( SOCIALPROMOTION_PLUGIN_DIR . 'includes/frontend/frontend-cartform.php');
      }
     //printf($frontendCartform);
    }
    add_action( 'woocommerce_before_cart_contents', 'prefix_after_cart_item_name', 10, 2 );
    
    function socialhero_load_scripts() {
        include( SOCIALPROMOTION_PLUGIN_DIR . 'includes/frontend/assets/js/socialheroJs.php');
    }
    add_action( 'wp_footer', 'socialhero_load_scripts' );   
    
    //function for add style
    function add_socialhero_stylesheet() {
    	wp_enqueue_style( 'socialhero-style', plugins_url( '/assets/css/socialhero.css' , __FILE__ ), array());
    }
    add_action( 'wp_enqueue_scripts', 'add_socialhero_stylesheet');   
    
    //apply discount to cart item
 
    add_action("wp_ajax_socialhero_apply_discount", "socialhero_apply_discount", 10);
    add_action("wp_ajax_nopriv_socialhero_apply_discount", "socialhero_apply_discount", 10);
    
    function socialhero_apply_discount() {
      global $wpdb;
      $discountTableName = $wpdb->prefix . 'discount_code';
      $result = array(sanitize_text_field($_POST['res']));
      $barCodeCardHolder = sanitize_text_field($_POST['barCodeCardHolder']);

      foreach($result as $key => $data){
         $apiToken = esc_html($data['apiToken']);
         $prizeAllow = esc_html($data['redimir']['prize']);
         $redime = esc_html($data['redimir']['redime']);
      }
      if($redime != 'true'){
          $response = ['status' => 'error','message' => 'Something Wrong!'];
          echo json_encode($response); // error show
      }
      else
      {
        $checkDisocuntStatus = $wpdb->prepare($wpdb->get_results("SELECT discount_status FROM $discountTableName WHERE client_number = '".$barCodeCardHolder."' "));
        $disocuntStatusSountRow =  count($checkDisocuntStatus);
        
        if($disocuntStatusSountRow > 0 && $checkDisocuntStatus[0]->discount_status == 'true')
        {
            $discount_code = $wpdb->update($discountTableName, array('apiToken'=>$apiToken,'code'=>$apiToken), array('client_number'=>$barCodeCardHolder));    
            $message = "discount apply!";
        } else
        {
            $discount_code = $wpdb->insert( $discountTableName, array(
                'discount_price' => $prizeAllow, 
                'apiToken' => $apiToken,
                'client_number' => $barCodeCardHolder, 
                'code' => $apiToken,
                'discount_status' => 'true', 
                'order_id' => ''
            ));
            $message = "discount apply!";
        }     
        if($discount_code){
            $response = ['code' => $apiToken,'status' => 'success','message'=> $message];
            echo json_encode($response); // coupon success
            WC()->session->set( 'code' , $apiToken );
        }
      }
      die;
    }

        function social_woocommerce_filter_checkout_for_coupons( $subtotal, $compound, $cart ) {
            global $wpdb;
            $code = WC()->session->get('code');
            $tbName = $wpdb->prefix . 'discount_code';
            $checkDiscountCoupon = $wpdb->prepare($wpdb->get_results("SELECT discount_price FROM $tbName WHERE code = '".$code."' && discount_status ='true'"));
            $countRow = count($checkDiscountCoupon);
            
            // We only need to add a store credit coupon if they have store credit
            if($code && $countRow > 0){
                // Your logic to get store credit value for a user will go here
                $store_credit = $checkDiscountCoupon[0]->discount_price;            
                // Setup our virtual coupon
                $coupon_name = '';
                $coupon = array($coupon_name => $store_credit);
                // Apply the store credit coupon to the cart & update totals
                $cart->applied_coupons = array($coupon_name);
                $cart->set_discount_total($store_credit);
                $cart->set_total( $cart->get_subtotal() - $store_credit);
                $cart->coupon_discount_totals = $coupon;
            }
            return $subtotal; 
        }
        
        add_filter( 'woocommerce_cart_subtotal', 'social_woocommerce_filter_checkout_for_coupons', 10, 3 );
        add_action( 'woocommerce_checkout_order_processed', 'socialhero_orderplace', 10, 3);
        function socialhero_orderplace($order_id){
            global $wpdb;
            $apiToken = WC()->session->get('code');
            $tbName = $wpdb->prefix . 'discount_code';
            $wpdb->update($tbName, array('discount_status'=>'false','order_id'=>$order_id), array('discount_status'=>'true','code'=>$apiToken));
            $rand_code = rand(100000000000,999999);
            //saleConfirm
            $mode = esc_attr(get_option('sh-mode'));
            if($mode == 'test'){
            $folio = "SHWP".$rand_code;
			$data = [
				'apiToken' => $apiToken,
				'status' => 'Done',
				'folio' => $folio
			];
            } 
            else 
            {
			$data = [
				'apiToken' => $apiToken,
				'status' => 'Done',
				'folio' => "#".$order_id
			];   
			}

            $payload = esc_html(json_encode($data));

            $url = "https://api.socialhero.com.mx/APIComercios/public/oauth/product/productActualization";
            $arg = array(
                'method' => 'POST', 
                'timeout' => 45, 
                'httpversion' => '1.0', 
                'blocking' => true, 
                'headers' => array('Content-Type: application/json'),
                'body' => $payload,
                );
            $resp = wp_remote_post( esc_url($url), $arg);
            $response = $resp['body'];
			$result = json_decode($response);
            if($result->status == 'success' && $result->message == 'Transacción completada')
            {
                WC()->session->__unset('code');
            }
        }
?>