/**
 * Amasty Delivery Date Delivery Configuration Option Component
 */

define([
    'underscore',
    'uiComponent'
], function (
    _,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            disabled: false,
            showSpinner: true,
            loading: false,
            indexField: 'id',
            template: 'Amasty_DeliveryDateManager/links-grid-row/links-grid-cell/links-grid-cell'
        },

        initObservable: function () {
            this._super()
                .observe(['disabled', 'selectValue', 'loading']);

            return this;
        }
    });
});
