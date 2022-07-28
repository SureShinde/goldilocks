define([
    'moment',
    'ko',
    'underscore',
    'jquery',
    'mage/translate',
    'mage/calendar'
], function (moment, ko, _, $, $t) {
    'use strict';

    var defaults = {
            dateFormat: 'mm\/dd\/yyyy',
            showsTime: false,
            timeFormat: null,
            buttonImage: null,
            showOn: 'both',
            buttonImageOnly: false,
            yearRange: 'c+0:c+1',
            drawYear: new Date().getFullYear(),
            drawMonth: new Date().getMonth() + 1,
            minDate: 0,
            buttonText: $t('Select Date'),
            currentText: $t('First Available Day')
        },
        map = {
            'D': 'd',
            'M': 'm'
        };

    ko.bindingHandlers.amastydatepicker = {
        init: function (el, valueAccessor) {
            var config = valueAccessor(),
                observable,
                date,
                options = {};

            _.extend(options, defaults);

            if (typeof config === 'object') {
                observable = config.storage;

                _.extend(options, config.options);
            } else {
                observable = config;
            }

            /*
             * Prepare format for calendar lib.
             * notice: it is not last prepare. in calendar.js short year pattern will be transferred to long.
             *      Always long year format on frontend.
             */
            _.each(map, function (momentFormat, mage) {
                options.dateFormat = options.dateFormat.replace(new RegExp(mage, 'g'), momentFormat);
            });

            // initialize datepicker
            $(el).calendar(options);

            // set initial calendar value (default)
            date = moment(observable(), config.elem.pickerDateTimeFormat);
            observable() && $(el).datepicker('setDate', date.format(config.elem.outputDateFormat));
            $(el).blur();

            ko.utils.registerEventHandler(el, 'change', function () {
                var momentDate;

                observable(this.value);

                if (this.value !== observable()) {
                    momentDate = moment(observable(), config.elem.pickerDateTimeFormat);
                    $(this).datepicker('setDate', momentDate.format(config.elem.outputDateFormat));
                }
            });
        }
    };
});
