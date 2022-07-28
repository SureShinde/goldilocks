define([
    'jquery',
    'Amasty_DeliveryDateManager/js/action/datepicker',
    'mage/calendar'
], function ($, datepickerActions) {
    'use strict';

    $.widget('amdelivery.timepicker', $.mage.calendar, {
        options: {
            beforeShow: datepickerActions.toggleCustomCssClass.bind(this, 'amdelivery-timepicker-nocurrent', true),
            onClose: datepickerActions.toggleCustomCssClass.bind(this, 'amdelivery-timepicker-nocurrent', false)
        }
    });

    return $.amdelivery.timepicker;
});
