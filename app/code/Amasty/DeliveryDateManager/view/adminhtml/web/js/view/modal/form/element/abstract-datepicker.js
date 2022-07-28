/**
 * Developed as improvement of magento datepicker binding, which not updated input value when provider value updated
 */

define([
    'jquery'
], function (jQuery) {
    'use strict';

    return {
        defaults: {
            listens: {
                '${ $.provider }:data.clear': 'clear'
            }
        },
        inputSelectorPattern: '[data-bind*=datepicker][name={inputName}]',
        inputSelector: '',

        initialize: function () {
            this._super();

            this.inputSelector = this.inputSelectorPattern.replace('{inputName}', this.inputName);
        },

        overload: function () {
            this._super();

            jQuery(this.inputSelector).val(this.shiftedValue());
        },

        clear: function () {
            this._super();

            jQuery(this.inputSelector).val('');

            return this;
        }
    };
});
