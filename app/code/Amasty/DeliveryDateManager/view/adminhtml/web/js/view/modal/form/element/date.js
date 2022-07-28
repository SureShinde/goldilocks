define([
    'Magento_Ui/js/form/element/date',
    'Amasty_DeliveryDateManager/js/view/modal/form/element/abstract-datepicker'
], function (Date, AbstractDatepicker) {
    'use strict';

    return Date.extend(AbstractDatepicker).extend({
        prepareDateTimeFormats: function () {
            this._super();

            // Fix datepicker bug when value stored in locale date format
            this.outputDateFormat = this.inputDateFormat;
            this.validationParams.dateFormat = this.outputDateFormat;
        }
    });
});
