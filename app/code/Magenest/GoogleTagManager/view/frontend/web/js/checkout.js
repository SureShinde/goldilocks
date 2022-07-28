
/* global define, window, dataLayer */
define([
    'underscore'
], function () {
    'use strict';

    var trackCheckout = {
        options: {
            currencyCode: '',
            products: [],
            checkoutSteps: []
        },
        step: 1,

        init: function (options) {
            window.dataLayer = window.dataLayer || [];
            trackCheckout.options = options;

            window.addEventListener('hashchange', trackCheckout.pushData);

            this.pushData();
        },

        pushData: function () {
            var eventData = {
                'event': 'checkout',
                'ecommerce': {
                    'currencyCode': trackCheckout.options.currencyCode,
                    'checkout': {
                        'actionField': trackCheckout.getCurrentStep(),
                        'products': trackCheckout.options.products
                    }
                }
            };

            dataLayer.push(eventData);
        },

        getCurrentStep: function () {
            var checkoutSteps = trackCheckout.options.checkoutSteps,
                uriPath = window.location.href.split('/checkout')[1];

            uriPath = uriPath.replace(/\/|#/g, '');

            if (uriPath === '') {
                uriPath = (checkoutSteps['shipping'] < checkoutSteps['payment']) ? 'shipping' : 'payment';
            }

            trackCheckout.step = checkoutSteps[uriPath];

            return {
                'step': trackCheckout.step.toString()
            };
        }
    };

    return function (options) {
        trackCheckout.init(options);
    };
});
