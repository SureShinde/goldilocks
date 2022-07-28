define([
    'Amasty_DeliveryDateManager/js/view/form/element/time-picker',
    'Amasty_DeliveryDateManager/js/view/modal/form/element/abstract-datepicker'
], function (Time, AbstractDatepicker) {
    'use strict';

    return Time.extend(AbstractDatepicker);
});
