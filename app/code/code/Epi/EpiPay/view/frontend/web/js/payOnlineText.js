define([
    'jquery',
    'jquery/ui'
], function(
    $,
    ){
    "use strict";
    
       $('body').click(function(){
        var activePaymentsMethod= $(".payment-group").find("div.payment-method").length;
                if(activePaymentsMethod==1){
                    $("div.payment-method div.payment-method-title label").remove("label");
                    $("div.payment-method div.payment-method-title").html("Pay Online");
                }
       })
}); 




