/**
 * Info List Table Component
 */
define([
    'jquery',
    'uiComponent',
    'ko',
    'underscore'
], function ($, Component, ko, _) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_PreOrderAnalytic/components/info_list',
            templates: {
                icons: {
                    cart: 'Amasty_PreOrderAnalytic/icons/cart',
                    money: 'Amasty_PreOrderAnalytic/icons/money',
                    safety: 'Amasty_PreOrderAnalytic/icons/safety',
                    scheduler: 'Amasty_PreOrderAnalytic/icons/scheduler',
                    task: 'Amasty_PreOrderAnalytic/icons/task'
                }
            },
            modules: {
                form: 'index = amasty_preorder_analytic_form'
            },
            systemData: [
                'date_range'
            ]
        },

        /**
         * Init observable variables
         *
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe({
                    elems: []
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

            self.initData(self.source.data);
        },

        /**
         * Initialization cards items data
         */
        initData: function (data) {
            var self = this;

            self._clearData();

            _.each(data, function (value, key) {
                if (self.systemData.indexOf(key) === -1) {
                    self.elems.push({
                        title: self._getTitle(key),
                        value: value,
                        icon: self._getIcon(key),
                        type: key
                    });
                }
            });
        },

        /**
         * Clearing cards items data
         */
        _clearData: function () {
            this.elems([]);
        },

        /**
         * Generating item title
         *
         * @returns {String} key - current item key
         * @return {String} title - current item title
         */
        _getTitle: function (key) {
            var title = '';

            switch (key) {
                case 'preorder_customer':
                    title = 'Unique Customers';
                    break;
                case 'preorder_item':
                    title = 'Products Pre-Ordered';
                    break;
                case 'preorder_pending':
                    title = 'Pre-Orders Pending';
                    break;
                case 'preorder_placed':
                    title = 'Pre-Orders Placed';
                    break;
                case 'preorder_revenue':
                    title = 'Pre-Orders Revenue';
                    break;
                default:
                    break;
            }

            return title;
        },

        /**
         * Generating item icon path
         *
         * @returns {String} key - current item key
         * @return {String} path to icon template
         */
        _getIcon: function(key){
            var icon = '';

            switch (key) {
                case 'preorder_customer':
                    icon = this.templates.icons.safety;
                    break;
                case 'preorder_item':
                    icon = this.templates.icons.cart;
                    break;
                case 'preorder_pending':
                    icon = this.templates.icons.scheduler;
                    break;
                case 'preorder_placed':
                    icon = this.templates.icons.task;
                    break;
                case 'preorder_revenue':
                    icon = this.templates.icons.money;
                    break;
                default:
                    break;
            }

            return icon;
        }
    });
});
