define([
    'Magento_Ui/js/grid/columns/select'
], function (Column) {
    'use strict';

    return Column.extend({

        /**
         * @returns {String}
         */
        getLabel: function () {
            var label = this._super();

            return label.split(', ').join('');
        }
    });
});
