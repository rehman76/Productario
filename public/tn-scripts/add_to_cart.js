/**
 * Created by aliraza on 06/10/2021.
 */
(function(){
   // //// Get add_to_cart parameter value from the URL
   // var products = null;
   // var coupon_code = null
   // var is_cart_created = null
   // tmp = [];
   //
   // var items = window.location.search.substr(1).split("&");
   // /// check if url have the get parameters
   // if(items[0]!==''){
   //    for (var index = 0; index < items.length; index++) {
   //       tmp = items[index].split("=");
   //       if (tmp[0] === 'add_to_cart') products = decodeURIComponent(tmp[1]);
   //       if (tmp[0] === 'coupon_code') coupon_code = decodeURIComponent(tmp[1]);
   //       if (tmp[0] === 'is_cart_created') is_cart_created = true;
   //    }
   //    console.log(products)
   //    console.log(items)
   //    console.log(coupon_code)
   //
   //    /// if add_to_cart exists in parameters
   //    if(products){
   //       /// Convert value to array of product ids
   //       var productIds = products.split(',');
   //       /// if more than one product reload the page
   //
   //       // if(!is_cart_created){
   //       //    /// add first product to cart
   //       //    // sendToCart(productIds[0], 1, productIds.length, 0)
   //       //    window.location = window.location.href + '&is_cart_created=true'
   //       // }
   //       //
   //       // if(productIds.length > 1 && !is_cart_created){
   //       //    // window.location = window.location.href + '&is_cart_created=true'
   //       // }
   //
   //       // if (productIds.length > 1 && is_cart_created){
   //       for (var index = 0; index < productIds.length; index++) {
   //          console.log(productIds[index])
   //          setTimeout( sendToCart(productIds[index], 1, productIds.length, index), 5000);
   //       }
   //       // }
   //
   //    }
   //
   // }

   sendToCart()
   function sendToCart() {
      $.ajax({
         method: "GET",
         url: "/comprar/323108870-3,321400538-1",
         xhrFields: {
            withCredentials: true
         },
         success: function (response) {
            if (response.success) {
               console.log('success')
               console.log(response)
               console.log(current_index)
               // if((current_index + 1) == (product_count)){
               //    //redirect to checkout page
               //    // window.location = '/checkout/v3/start/'+ response.cart.id + '/'+ response.cart.token+'?coupon_code='+coupon_code
               // }
            }
         },
         error: function (a) {
            console.log('error')
            console.log(a)
         }
      });
   }
})();
