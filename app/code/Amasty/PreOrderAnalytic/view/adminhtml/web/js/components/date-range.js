/**
 * Date Range Component
 */
define([
    'jquery',
    'uiComponent',
    'ko',
    'underscore',
    'rjsResolver',
    'mage/calendar'
], function ($, Component, ko, _, resolver) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_PreOrderAnalytic/components/date_range',
            templates: {
                icons: {
                    calendar: 'Amasty_PreOrderAnalytic/icons/calendar'
                }
            },
            modules: {
                form: 'index = amasty_preorder_analytic_form',
                infoList: 'index = ampreorderan_info_list'
            },
            value: {
                from: ko.observable(),
                to: ko.observable()
            }
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            var self = this;

            self._super();

            resolver(function () {
                self.form = self.form();
                self.infoList = self.infoList();
            });
        },

        /**
         * Date picker initialization
         *
         * @param {Object} node
         */
        initDatePicker: function (node) {
            var self = this;

            $(node).dateRange({
                onClose: function (value, event) {
                    if ($(event.input).attr('name') === 'from') {
                        self.value.from(value);
                    } else {
                        self.value.to(value);
                    }
                    self.getFilterData(self.infoList.initData.bind(self.infoList));
                },
                dateFormat: 'yy-mm-dd',
                from: {
                    id: 'from'
                },
                to: {
                    id: 'to'
                }
            });
            this.value.from(this.source.data.date_range.from);
            this.value.to(this.source.data.date_range.to);
        },

        /**
         * Getting new Info data by new date filter params
         *
         * @param {Object} callback
         */
        getFilterData: function (callback) {
            $.ajax({
                url: this.form.reloadUrl,
                method: 'POST',
                data: {
                    from: this.value.from(),
                    to: this.value.to()
                },
                showLoader: true
            }).done(function (data) {
                callback(data)
            });
        }
    });
});
