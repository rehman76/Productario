// Immediately-invoked function expression
(function() {
    // var coupon_code = null
    // // Load the script
    // tmp = [];
    // var items = window.location.search.substr(1).split("&");
    //
    // if(items[0]!=='') {
    //     for (var index = 0; index < items.length; index++) {
    //         tmp = items[index].split("=");
    //         if (tmp[0] === 'coupon_code') coupon_code = decodeURIComponent(tmp[1]);
    //     }
    // }
    //
    // console.log(LS.cart.id)
    // console.log(coupon_code)
    //
    // if(coupon_code){
    //     const script = document.createElement("script");
    //     script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js';
    //     script.type = 'text/javascript';
    //     script.addEventListener('load', () => {
    //         console.log(`jQuery ${$.fn.jquery} has been loaded successfully!`);
    //     console.log(window.csrfToken);
    //     $.ajax({
    //         method: "POST",
    //         url: "/checkout/v3/orders/"+LS.cart.id+"/coupon",
    //         contentType: "application/json; charset=utf-8",
    //         dataType: "json",
    //         data: JSON.stringify({
    //             cartId: LS.cart.id,
    //             coupon_code: coupon_code
    //         }),
    //         headers: {
    //             'csrf-token': window.csrfToken
    //         },
    //         success: function (response) {
    //             if (response.success) {
    //                 window.location = window.location.href.split('?')[0]
    //             }
    //         },
    //         error: function (a) {
    //             console.log('error')
    //             console.log(a)
    //         }
    //     });
    // });
    //     document.head.appendChild(script);
    // }

    emailField = document.getElementById('contact.email');
    emailField.removeAttribute('disabled');
    emailField.classList.remove('disabled');
})();