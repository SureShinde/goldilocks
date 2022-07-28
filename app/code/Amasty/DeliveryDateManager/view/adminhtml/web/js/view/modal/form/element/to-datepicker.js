define([
    'Amasty_DeliveryDateManager/js/view/modal/form/element/date'
], function (Datepicker) {
    'use strict';

    return Datepicker.extend({
        /**
         * Set minDate to "To" datepicker
         * @param {Date|String} date
         * @returns {void}
         */
        setToMinDateEqualFromDate: function (date) {
            if (new Date(date) > jQuery(this.inputSelector).datepicker('getDate')) {
                this.onShiftedValueChange(date);
            }

            jQuery(this.inputSelector).calendar('option', 'minDate', date);
        }
    });
});
