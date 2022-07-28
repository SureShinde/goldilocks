
/* global window, define */
define([
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/lib/core/class',
    'underscore'
], function (customerData, Class, _) {
    'use strict';

    return Class.extend({
        customerDataKey: 'google-tag-manager-product-info',

        /**
         * Initialize subscriber
         */
        initialize: function () {
            this._super();

            /**
             * Subscribes to changes in user cart and reports it to the dataLayer
             */
            var productInfo = customerData.get(this.customerDataKey);

            productInfo.subscribe(this.onDataChange, this);
        },

        /**
         * Handler for data changes
         *
         * @param productInfo
         */
        onDataChange: function (productInfo) {
            /**
             * Only trigger on content
             */
            if (!_.isEmpty(productInfo) && !_.isEmpty(productInfo.data) && !_.isEmpty(productInfo.data.products)) {
                var result = productInfo.data;

                if (result['products']['added_item_data']) {
                    this.reportAddedProducts(result['products']['added_item_data'], result['currencyCode']);
                }

                if (result['products']['removed_item_data']) {
                    this.reportRemovedProducts(result['products']['removed_item_data'], result['currencyCode']);
                }

                /**
                 * When JS bundling is enabled, the subscriber is always called for some reason,
                 * This clears the data, preventing data from being reported more than once.
                 */
                customerData.set(this.customerDataKey, {});
            }
        },

        /**
         * Handler for added products
         *
         * @param products
         * @param currencyCode
         */
        reportAddedProducts: function (products, currencyCode) {
            var eventType = 'addToCart',
                actionType = 'add';

            this.pushEvent(eventType, actionType, products, currencyCode);
        },

        /**
         * Handler for removed products
         *
         * @param products
         * @param currencyCode
         */
        reportRemovedProducts: function (products, currencyCode) {
            var eventType = 'removeFromCart',
                actionType = 'remove';

            this.pushEvent(eventType, actionType, products, currencyCode);
        },

        /**
         * Push event data to the dataLayer
         *
         * @param eventType Datalayer Event
         * @param actionType Referred to as "actionFieldObject" in the GTM EEC documentation
         * @param products Array of product data
         * @param currencyCode CurrencyCode for datalayer
         */
        pushEvent: function (eventType, actionType, products, currencyCode) {
            var eventData = {
                'event': eventType,
                'ecommerce': {
                    'currencyCode': currencyCode
                }
            };

            eventData['ecommerce'][actionType] = {
                'products': products
            };

            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push(eventData);
        }
    });
});
