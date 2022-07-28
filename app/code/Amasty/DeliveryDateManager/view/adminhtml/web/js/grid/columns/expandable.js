define([
    'jquery',
    'underscore',
    'Magento_Ui/js/grid/columns/column'
], function ($, _, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            expandTmpl: 'Amasty_DeliveryDateManager/grid/cells/expandable/default',
            expandControl: false,
            detailedDataIndex: '',
            // [<[{ index: <field_index>, label: <field label>}]>, ...]
            detailedDataMap: [],
            rowSelectorPattern: "tr[data-repeat-index='{rowIndex}']",
            cellSelector: '[data-amdelivery-js="toggle-element"]',
            toggleClass: '-expanded',
            listens: {
                '${ $.provider }:reloaded': 'onReloaded'
            }
        },

        /**
         * @param {Object} record
         * @return {Array}
         */
        getDetailedInfoData: function (record) {
            var detailedData = record[this.detailedDataIndex];

            if (!_.isArray(detailedData)) {
                detailedData = [detailedData];
            }

            return detailedData ?? [];
        },

        /**
         * @param {String|Number|Array} itemValue
         * @param {?Object} mapItem
         * @return {String|Number}
         */
        getInfoValue: function (itemValue, mapItem) {
            if (mapItem && mapItem.valuesMap && _.has(mapItem.valuesMap, itemValue)) {
                itemValue = mapItem.valuesMap[itemValue];
            }

            return itemValue ? itemValue : '-';
        },

        /**
         * Expand/collapse row
         *
         * @param {Number} rowIndex
         */
        toggleRow: function (rowIndex) {
            var row, cells;

            if (!this.expandControl) {
                return;
            }

            row = $(this.rowSelectorPattern.replace('{rowIndex}', rowIndex));
            cells = row.find(this.cellSelector);

            cells.toggleClass(this.toggleClass);
        },

        /**
         * Reset cells state to collapsed after grid reloading
         */
        onReloaded: function () {
            $(this.cellSelector).removeClass(this.toggleClass);
        },

        /**
         * @param {*} toCheck
         * @return {Boolean}
         */
        hasData: function (toCheck) {
            return !_.isEmpty(toCheck);
        }
    });
});
