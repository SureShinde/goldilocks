/**
 * Amasty Delivery Date Delivery Configuration Abstract Actions Component
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
            template: 'Amasty_DeliveryDateManager/links-grid-row/links-grid-cell/elements/cell-actions',
            imports: {
                selectValue: '${ $.parentName }:selectValue'
            }
        },

        initialize: function () {
            this._super();

            // do something

            return this;
        },

        initObservable: function () {
            this._super();

            // do something

            return this;
        }
    });
});
