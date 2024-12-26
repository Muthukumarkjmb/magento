define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, alert) {
    'use strict';

    return function (config) {
        $(document).on('ajaxComplete', function (event, xhr, settings) {
            console.log(settings,"settings");
            if (settings.url.indexOf('/shipping-information') !== -1 && xhr.status === 400) {
                var response = JSON.parse(xhr.responseText);
                if (response && response.message) {
                    alert({
                        title: $.mage.__('Address Validation Error'),
                        content: $.mage.__(response.message),
                        modalClass: 'custom-alert',
                        actions: {
                            always: function(){}
                        }
                    });
                }
            }
        });
    }
});

