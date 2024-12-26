define([
    "jquery",
    "Magento_Checkout/js/checkout-data",
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Customer/js/model/customer',
], function(
    $,
    checkoutData,
    selectShippingMethodAction,
    customer,
    ){
    "use strict";
     $(function(){
            var shippingMethod=$.cookieStorage.get("shippingMethod")
            if(shippingMethod && customer.isLoggedIn()){
                var shippingMethodCode=shippingMethod['carrier_code'] + '_' + shippingMethod['method_code'];
                // console.log($.cookieStorage.get("shippingMethod"));
                    $(`input[value= ${shippingMethodCode}]`).attr('checked',true);
                    selectShippingMethodAction(shippingMethod);
                    checkoutData.setSelectedShippingRate(shippingMethodCode);
                    $.cookieStorage.set("shippingMethod",null);
                // console.log('hwll this is if',$.cookieStorage.get("shippingMethod"));
                }
            else {
                $.cookieStorage.set("shippingMethod",null);
                // console.log('hwll',$.cookieStorage.get("shippingMethod"));
            }
       })
}); 




