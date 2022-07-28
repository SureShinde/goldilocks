/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/sidebar',
    'uiRegistry',
    'moment',
], function ($, Component, quote, stepNavigator, sidebarModel, registry, moment) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-information'
        },

        /**
         * @return {Boolean}
         */
        isVisible: function () {
            return !quote.isVirtual() && stepNavigator.isProcessed('shipping');
        },

        /**
         * @return {String}
         */
        getShippingMethodTitle: function () {
            var shippingMethod = quote.shippingMethod(),
                shippingMethodTitle = '';

            if (!shippingMethod) {
                return '';
            }

            shippingMethodTitle = shippingMethod['carrier_title'];

            if (typeof shippingMethod['method_title'] !== 'undefined') {
                shippingMethodTitle += ' - ' + shippingMethod['method_title'];
            }

            return shippingMethodTitle;
        },

        /**
         * Back step.
         */
        back: function () {
            sidebarModel.hide();
            stepNavigator.navigateTo('shipping');
        },

        /**
         * Back to shipping method.
         */
        backToShippingMethod: function () {
            sidebarModel.hide();
            stepNavigator.navigateTo('shipping', 'opc-shipping_method');
        },

        getDateDelivery: function () {
            var dateComponent = registry.get({index: 'deliverydate_date'});
            if (dateComponent.shiftedValue()) {
                var date = moment(dateComponent.shiftedValue());
                return date.format("YYYY-MM-DD");
            } else {
                return '';
            }
        },

        getTimeDelivery: function () {
            var timeComponent = registry.get({index: 'deliverydate_time'});
            return timeComponent.getPreview();
        },

        isPickUpStore: function (){
            var shippingMethod = quote.shippingMethod();
            return shippingMethod && shippingMethod['carrier_code'] === 'amstorepickup';
        }
    });
});
