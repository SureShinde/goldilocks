/**
 * Last List Table Component
 */
define([
    'jquery',
    'uiComponent',
    'ko',
    'underscore',
    'rjsResolver',
    'Magento_Ui/js/lib/spinner'
], function ($, Component, ko, _, resolver, loader) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_PreOrderAnalytic/components/last_list',
            modules: {
                headerColumns: 'index = amasty_preorder_last_columns'
            },
            imports: {
                statusMap: 'index = amasty_preorder_status_deps:options'
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
                loader.hide();
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
         * Initialization row data
         */
        _initItems: function () {
            var self = this;

            _.each(self.source.data.items, function (item) {
                self._initStatusTitle(item);
                self.items.push(item);
            });
        },

        /**
         * Initialization item status title data
         *
         * @params {Object} item
         */
        _initStatusTitle: function (item) {
            var match = _.find(this.statusMap, function (status) {
                return item.status === status.value
            });

            item.status_title = match.label;
        }
    });
});
