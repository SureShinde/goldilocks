/**
 * Amasty Delivery Date Delivery Configuration Abstract Description Element
 */

define([
    'underscore',
    'uiElement'
], function (
    _,
    Element
) {
    'use strict';

    return Element.extend({
        defaults: {
            descriptionData: {},
            indexField: 'id',
            optionsMap: [],
            imports: {
                selectValue: '${ $.parentName }:selectValue'
            },
            listens: {
                '${ $.parentName }:selectValue': 'setDescriptionData',
                '${ $.externalProvider }:reloaded': 'updateDescriptionData'
            },
            modules: {
                externalSource: '${ $.externalProvider }'
            }
        },

        initialize: function () {
            this._super();

            this.setDescriptionData(this.selectValue());

            return this;
        },

        initObservable: function () {
            this._super()
                .observe(['selectValue', 'descriptionData']);

            return this;
        },

        /**
         * @param {Number} selectValue
         * @returns {void}
         */
        setDescriptionData: function (selectValue) {
            var descriptionObject = {},
                query = {};

            if (selectValue && !_.isEmpty(this.externalSource().data)) {
                query[this.indexField] = selectValue.toString();
                descriptionObject = _.findWhere(this.externalSource().data, query);
            }

            this.descriptionData(descriptionObject);
        },

        /**
         * @param {String} itemKey
         * @param {String|Number} itemValue
         * @return {String|Number}
         */
        getItemValue: function (itemKey, itemValue) {
            if (this.optionsMap
                && _.has(this.optionsMap, itemKey)
                && _.has(this.optionsMap[itemKey], itemValue)
            ) {
                itemValue = this.optionsMap[itemKey][itemValue];
            }

            return itemValue;
        },

        /**
         * @param {*} toCheck
         * @return {Boolean}
         */
        hasData: function (toCheck) {
            return !_.isEmpty(toCheck);
        },

        /**
         * Update description data after options reloaded
         *
         * @returns {void}
         */
        updateDescriptionData: function () {
            this.setDescriptionData(this.selectValue());
        }
    });
});
