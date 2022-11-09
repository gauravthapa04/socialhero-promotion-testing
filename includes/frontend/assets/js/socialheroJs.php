<?php
if(esc_attr( get_option('sh-mode'))== 'test'){
    $api_url = "https://api.socialhero.com.mx/APIComercios-Test/public/oauth/sale/saleValidation";
}else{
    $api_url = "https://api.socialhero.com.mx/APIComercios/public/oauth/sale/saleValidation";
}
?>

<script type="text/javascript">
    document.addEventListener('click',function(event){
    if(event.target.classList.contains('socialhero_btn')){
            var api__url = "<?php echo esc_url($api_url); ?>";
            var product_info = document.getElementById('product_info').value;
            var cart_item_data = JSON.parse(product_info);
            
            document.getElementById('socialhero-error').style.display = 'none';
            let socialheroInputValue = document.getElementById('socialhero_barCodeCardHolder').value;
            let currency_symbol = document.getElementById('product_info');
            var cs = currency_symbol.getAttribute('data-currency');
            
            if(socialheroInputValue != ''){
            var myHeaders = new Headers();
            myHeaders.append("Content-Type", "application/json");
            myHeaders.append("Cookie", "XSRF-TOKEN=eyJpdiI6InNoWGpDQlM5U2NXY3dmMkpcL1wvYTRiZz09IiwidmFsdWUiOiJqeGlCdDIrUXVWSWdyUGoyVmc5YUpZVFJLQ1wvTFYwQWdwaVdJVUFsYk5qNjkyRDI3RDNMU3F1U3lDZHRab0kzZ05WbmQ2N2JVdmZKbkZNVWtOMURLSWc9PSIsIm1hYyI6IjVkYmQ0ZTNjMjk5NjQzMjU2Y2Q4MmNjM2Y3MWYxYjQxZTExMTg5YThkNTNlZWU4Yzk5OWFhYjIzMTZhMjk2NjYifQ%3D%3D; laravel_session=eyJpdiI6IjA5bTlHODBFSG5TSlVDemJqM1V6Y2c9PSIsInZhbHVlIjoieThNZWR2a2VSUzRSMDlzaXlBZFNRdFExSzlXU01FdTNLTzA2dmhlY1R0bkdFWEpwVVdFdjVrbmY4OVNcL1ZETmJNUnFKaXhJUjBQU3NQU0RKVUJPKyt3PT0iLCJtYWMiOiI5ZjAwZWI3YTQxMjEyNTAzNzA0ZWYxOGE2ODg1MjVlNDEwOWQ1YmVhY2I2NjJkODAwZjM0MjQ4MDcyMDhlOTIwIn0%3D");
            
            var total_cart = jQuery(".order-total .woocommerce-Price-amount").text().replace(cs,'');
            var raw = JSON.stringify({
              "branchId": "<?php echo esc_attr( get_option('sh-branch_id') ); ?>",
              "barCodeCardHolder": socialheroInputValue,
              "origin": "<?php echo esc_attr( get_option('sh-origin') ); ?>",
              "saleTotal": total_cart,
              "products": cart_item_data
            });
            
            var requestOptions = {
              method: 'POST',
              headers: myHeaders,
              body: raw,
              redirect: 'follow'
            };
            
            fetch(api__url, requestOptions)
              .then(response => response.text())
              .then((result) => {
                  
                    var res = JSON.parse(result);
                    if(res.status == 'fail'){
                        document.getElementById('socialhero-error').style.display = 'block';
                        document.getElementById('socialhero-error').innerHTML = res.message; 
                    }
                    else if(res.redimir.prizeAllow <= -1){
                        document.getElementById('socialhero-error').style.display = 'block';
                        document.getElementById('socialhero-error').innerHTML = 'Discount price is less then equal to 0';                        
                    }
                    else if(res.status == 'success'){
                        
                        //res.push(socialheroInputValue);
                        
                        jQuery.ajax({
                             type : "post",
                             dataType : "json",
                             url : "<?php echo admin_url('admin-ajax.php');?>",
                             data : { action: "socialhero_apply_discount", res,'barCodeCardHolder':socialheroInputValue},
                             success: function(dis_response) {
                                 console.log(dis_response);
                                // debugger;
                                 var status = dis_response['status'];
                                 var message = dis_response['message'];
                                 var code = dis_response['code'];
                                 //console.log(status);
                                //  debugger;
                                 if(status == 'success'){
                                     window.location.reload();
                                 } else {
                                    document.getElementById('socialhero-error').style.display = 'block';
                                    document.getElementById('socialhero-error').innerHTML = message;                                    
                                 }
                             }
                        });   
                    }
              })
              .catch((error) =>
                {
                    document.getElementById('socialhero-error').style.display = 'block';
                    document.getElementById('socialhero-error').innerHTML = error;
                });
            }
            else {
                document.getElementById('socialhero-error').style.display = 'block';
                document.getElementById('socialhero-error').innerHTML = 'Please enter the barcode!';
            }
        }
    });
</script>