define([
    'jquery',
    'moment',
    'mageUtils'
], function ($, moment, utils) {
    'use strict';

    return function (validator) {
        /**
         * Is date same or after current date
         */
        validator.addRule(
            'date-same-or-after-current',
            function (value, params, additionalParams) {
                var test = moment(value, utils.convertToMomentFormat(additionalParams.dateFormat)),
                    todayDate = new Date();

                return test.isSame(todayDate, 'day') || test.isAfter(todayDate, 'day');
            },
            $.mage.__('Please enter a date greater than or equal to the current date.')
        );

        return validator;
    };
});
