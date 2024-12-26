define([
    'Magento_Checkout/js/view/shipping',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/cart/totals-processor/default',
    'ko'
], function (Component, quote, defaultTotal, ko) {
    'use strict';

    return Component.extend({
        initialize: function () { console.log('Custom shipping component initialized');
            this._super();
            var self = this;

            // Ensure order summary visibility is controlled by the grand total
            self.isOrderSummaryVisible = ko.observable(true);

            // Delay to ensure it runs after all other scripts
            
                // Subscribe to shipping method changes and update totals
                quote.shippingMethod.subscribe(function () {
                    defaultTotal.estimateTotals();
                });

                // Ensure order summary is visible based on grand total
                quote.totals.subscribe(function (totals) {
                    console.log('Totals updated:', totals);
                    if (totals && totals.grand_total) {
                        console.log('Setting order summary visible');
                        self.isOrderSummaryVisible(true);
                    } else {
                        console.log('Hiding order summary');
                        self.isOrderSummaryVisible(false);
                    }
                });
           

            return this;
        }
    });
});
