define([
    'jquery',
    'moment',
    'mage/translate'
 ],
  function($){
        'use strict';

        return function(validator){
           validator.addRule(
            'validate-phone-number',
            function(value){
    
                return ( /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(value) && value != "000-000-0000");
            },
            $.mage.__('Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.')
           );

           return validator;
        }
    }
);