<?php
 global $woocommerce;
 global $wpdb;
 $items = $woocommerce->cart->get_cart();
 $spTableName = $wpdb->prefix . 'discount_code';
 $product_info = [];

    //get the code of api
    $mode = esc_attr(get_option('sh-mode'));
        
    if($mode == 'test'){
        $product_info = array(
            array('id_product'=>'309','name'=>'Doritos','price'=>'17.5','patent'=>'SVP-1208','code'=>'7501011123588','status'=>'success','piece'=>1,'marca_id'=>0,'categoria_id'=>100),
            array('id_product'=>'317','name'=>'Coca lata','price'=>'14.5','patent'=>'SVP-1208','code'=>'7501055300075','status'=>'success','piece'=>1,'marca_id'=>0,'categoria_id'=>100)
            );
    } else {
    foreach($items as $item => $values) {
        $_product =  wc_get_product( $values['data']->get_id());
        $price = get_post_meta($values['product_id'] , '_price', true);
        $product_sku = get_post_meta( $values['product_id'], '_sku', true );
        $product_data = array('id_product'=>$values['product_id'],'name'=>$_product->get_title(),'price'=>$price,'code'=>$product_sku,'status'=>'success','piece'=>$values['quantity']);
        $product_info[]= $product_data;
    }
    }
    //check discount status
  $code = WC()->session->get('code'); 
  $checkDiscountStatus = $wpdb->prepare($wpdb->get_results("SELECT discount_status FROM $spTableName WHERE code = '".$code."' "));
 ?>

<div class="socialhero-api">
    <p id="socialhero-error" class="socialhero-error" style="display:none;"></p>
    <?php if($code && $checkDiscountStatus[0]->discount_status == 'true') { ?><p class="socialhero-couponapplied">Coupon Applied!</p><?php } ?>
    <label for="socialhero_barCodeCardHolder"></label>
    <div class="socialHeroApiInner">
        <input type="text" name="barCodeCardHolder" placeholder="INGRESA TU NÚMERO DE AFILIADO SOCIALHERO" id="socialhero_barCodeCardHolder">
        <input type="hidden" id="product_info" value='<?php echo json_encode($product_info);?>' data-currency="<?php echo get_woocommerce_currency_symbol();?>">
        <button type="button" class="button socialhero_btn">Aplicar Beneficios</button>
        <p style='margin-top: 15px;font-size: 14px;'>¿No estás afiliado? Afiliación gratuita en <a
        href='https://www.socialhero.com.mx/afiliate' target='_blank' style='color:
        #5F4690;'>https://www.socialhero.com.mx/afiliate</a></p>
    </div>
</div>