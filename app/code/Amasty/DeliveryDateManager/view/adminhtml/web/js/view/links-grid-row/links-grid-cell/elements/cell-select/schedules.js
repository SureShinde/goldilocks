define([
    'underscore',
    'Amasty_DeliveryDateManager/js/view/links-grid-row/links-grid-cell/elements/cell-select'
], function (_, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            allValuesDataScope: 'data.schedules',
            triggerKey: '${ $.allValuesDataScope }-filter-options',
            listens: {
                '${ $.provider }:${ $.dataScope }': 'triggerFiltering',
                '${ $.provider }:${ $.allValuesDataScope }-filter-options': 'onFilterOptions',
            }
        },

        prepareOptions: function (options) {
            return this._super(this.filterOptions(options));
        },

        triggerFiltering: function () {
            this.source.trigger(this.triggerKey);
        },

        onFilterOptions: function () {
            this.setOptions(this.prepareOptions(this.externalSource().data));
        },

        /**
         * @param {Array} currentOptions
         * @returns {Array}
         */
        filterOptions: function (currentOptions) {
            var selectedValues = [],
                value = this.source.get(this.dataScope);

            if (_.isEmpty(currentOptions)) {
                return currentOptions;
            }
            _.each(this.source.get(this.allValuesDataScope), function (rowData) {
                if (rowData[this.index] !== value) {
                    selectedValues.push(rowData[this.index]);
                }
            }.bind(this));

            if (!_.isEmpty(selectedValues)) {
                currentOptions = _.filter(currentOptions, function (option) {
                    return !_.contains(selectedValues, option[this.index]);
                }.bind(this));
            }

            return currentOptions;
        }
    });
});
