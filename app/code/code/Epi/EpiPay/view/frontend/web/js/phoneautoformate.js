define([
    "jquery",
    "jquery/ui"
], function($) {
    "use strict";
   
      $.widget('phoneautoformate.js', {
        _create: function() {
            var widget = this;
            var templateoptions = widget.options;
            $('body').on('input', templateoptions.phoneinput, function() {
               var number = $(this).val().replace(/[^0-9| ^-]/g, '');
                 if(number.length == 4 && number[3] != "-"){
                     number=number.substring(0, 3) + "-" + number.substring(3);
                }
                else if(number.length == 8 && number[7] != "-" ){
                   number=number.substring(0, 7) + "-" + number.substring(7);
                }
                else if(number.length>12){
                    number=number.substring(0,12);
                }
                $(this).val(number);
            });
            
        }
    });
    return $.phoneautoformate.js;
}); 
