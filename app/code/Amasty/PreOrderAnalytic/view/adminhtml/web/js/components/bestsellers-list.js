/**
 * Best Sellers List Table Component
 */
define([
    'jquery',
    'uiComponent',
    'ko',
    'underscore',
    'rjsResolver'
], function ($, Component, ko, _, resolver) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_PreOrderAnalytic/components/bestsellers_list',
            templates: {},
            modules: {
                headerColumns: 'index = amasty_preorder_bestseller_columns'
            }
        },

        /**
         * Init observable variables
         *
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe({
                    items: [],
                    titles: []
                });

            return this;
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            var self = this;

            self._super();

            resolver(function () {
                self.headerColumns = self.headerColumns();
                self._initTitles();
                self._initItems();
            })
        },

        /**
         * Initialization cards items data
         */
        _initTitles: function () {
            var self = this;

            _.each(self.headerColumns.elems(), function (item) {
                self.titles.push({
                    index: item.index,
                    label: item.label
                });
            });
        },

        /**
         * Initialization cards items data
         */
        _initItems: function () {
            var self = this;

            _.each(self.source.data.items, function (item) {
                self.items.push(item);
            });
        }
    });
});
