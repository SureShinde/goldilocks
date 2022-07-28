define([
    'jquery',
    'mage/translate',
    'mage/validation'
], function ($) {
    'use strict';

    var selected = [];

    $.validator.addMethod(
        'permission',
        function (selected, values, element) {
            element = $(element);

            var tables = element.closest('.admin__control-table-wrapper').children('table'),
                lastTable = tables[tables.length - 1],
                currentTable = element.closest('table').get(0),
                result = true;

            if (! values) {
                return true;
            }

            for (var value of values) {
                if (selected.includes(value)) {
                     result = false;
                     break;
                }
                selected.push(value);
            }

            if (currentTable === lastTable) {
                clearArray(selected);
            }

            return result;
        }.bind(null, selected),
        $.mage.__('Customer group must be unique.')
    );

    function clearArray (array) {
        while (array.length) {
            array.pop();
        }
    }
});
