define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/date',
    'Amasty_DeliveryDateManager/js/action/datepicker',
    'Magento_Ui/js/lib/validation/validator',
    './time-utils'
], function ($, _, dateComponent, datepickerActions, validator, timeUtils) {
    'use strict';

    return dateComponent.extend({
        defaults: {
            noCurrentButtonCssClass: 'amdelivery-timepicker-nocurrent'
        },

        initConfig: function () {
            validator.addRule(
                'amasty-time',
                function (value) {
                    return _.isEmpty(value)
                        || /^(([01]\d)|\d|2[0-3]):([0-5]\d)$/.test(value) // 24 hours format
                        || /^((0?[1-9]|1[012]):([0-5]\d)(\s[AP]M))$/i.test(value); // 12 hours format
                },
                $.mage.__('Please enter a valid time.')
            );

            this._super();

            return this;
        },

        initStatefull: function () {
            this._super();

            this.options.beforeShow = datepickerActions
                .toggleCustomCssClass.bind(this, this.noCurrentButtonCssClass, true);
            this.options.onClose = datepickerActions
                .toggleCustomCssClass.bind(this, this.noCurrentButtonCssClass, false);

            return this;
        },

        onValueChange: function (value) {
            var shiftedValue;

            if (value) {
                shiftedValue = timeUtils.timeHoursConverter(value);
            } else {
                shiftedValue = '';
            }

            if (shiftedValue !== this.shiftedValue()) {
                this.shiftedValue(shiftedValue);
            }
        },

        onShiftedValueChange: function (shiftedValue) {
            var value;

            if (shiftedValue) {
                value = shiftedValue;
            } else {
                value = '';
            }

            if (value !== this.value()) {
                this.value(value);
            }
        },

        prepareDateTimeFormats: function () {
            // Set empty date format to prevent wrong time parsing by moment
            // It's necessary while removing/dragging rows with timepicker
            this.options.dateFormat = '';

            this._super();

            this.pickerDateTimeFormat = this.pickerDateTimeFormat.trim();
        }
    });
});
