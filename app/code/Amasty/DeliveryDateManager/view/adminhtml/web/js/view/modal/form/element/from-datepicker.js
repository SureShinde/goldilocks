define([
    'Amasty_DeliveryDateManager/js/view/modal/form/element/date'
], function (Datepicker) {
    'use strict';

    return Datepicker.extend({
        defaults: {
            modules: {
                toDatepicker: '${$.toDatepickerName}'
            }
        },

        /**
         * Set "From" equal "To" if "To" less than "From"
         * @param {Date|String} date
         * @returns {void}
         */
        setFromEqualTo: function (date) {
            if (!this.toDatepicker() || !this.toDatepicker().validate().valid) {
                return;
            }

            this.toDatepicker().validate();

            if (new Date(date) < jQuery(this.inputSelector).datepicker('getDate')) {
                jQuery(this.inputSelector).datepicker('setDate', date);
                this.onShiftedValueChange(date);
            }
        }
    });
});
