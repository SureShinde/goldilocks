/**
 * Amasty Delivery Date Delivery Configuration Options Container Row Component
 */

define([
    'ko',
    'underscore',
    'uiComponent'
], function (
    ko,
    _,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_DeliveryDateManager/links-grid-row/links-grid-row',
            visible: true
        },

        initObservable: function () {
            this._super()
                .observe(['visible']);

            return this;
        }
    });
});
