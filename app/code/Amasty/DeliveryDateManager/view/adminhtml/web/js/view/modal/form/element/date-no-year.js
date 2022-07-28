/**
 * Datepicker without year
 */

define([
    'Amasty_DeliveryDateManager/js/view/modal/form/element/date',
    'Amasty_DeliveryDateManager/js/action/datepicker'
], function (Date, datepickerActions) {
    'use strict';

    return Date.extend({
        defaults: {
            hideYear: true,
            noYearCssClass: 'amdelivery-datepicker-noyear'
        },

        initStatefull: function () {
            this._super();

            if (this.hideYear) {
                this.options.beforeShow = datepickerActions
                    .toggleCustomCssClass.bind(this, this.noYearCssClass, true);
                this.options.onClose = datepickerActions
                    .toggleCustomCssClass.bind(this, this.noYearCssClass, false);
            }

            return this;
        }
    });
});
