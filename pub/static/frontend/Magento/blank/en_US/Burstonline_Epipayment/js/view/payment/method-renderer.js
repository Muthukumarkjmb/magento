define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'epipayment',
                component: 'Burstonline_Epipayment/js/view/payment/method-renderer/epipayment'
            }
        );
        return Component.extend({});
    }
);