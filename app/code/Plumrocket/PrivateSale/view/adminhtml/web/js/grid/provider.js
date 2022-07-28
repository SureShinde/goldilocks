/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

define([
    'Magento_Ui/js/grid/provider',
    'uiRegistry'
], function (Element, uiRegistry) {
    'use strict';

    return Element.extend({
        defaults: {
            bindedItems: [],
        },

        /**
         * Handles successful data reload.оке
         *
         * @param {Object} data - Retrieved data object.
         */
        onReload: function (data) {
            if (this.formProvider && data.items) {
                for (var [key, item] of Object.entries(data.items)) {
                    if (this.bindedItems.hasOwnProperty(item.entity_id)) {
                        data.items[key] = this.bindedItems[item.entity_id];
                    }
                }
            }

            return this._super(data);
        },

        bindItems: function (items) {
            for (var [key, item] of Object.entries(items)) {
                this.bindedItems[item.entity_id] = item;
                this.getFormProvider().data.flash_sale_data[item.entity_id]
                    = {
                    product_id: item.entity_id,
                    discount_amount_percent: item.discount_amount_percent,
                    sale_price: item.sale_price,
                    flash_sale_qty_limit: item.flash_sale_qty_limit
                };
            }

            return this;
        },

        getFormProvider: function () {
            if (! this.formProvider && this.internalProviderName) {
                this.formProvider = uiRegistry.get(this.internalProviderName);
                this.formProvider.data.flash_sale_data = {};
            }

            return this.formProvider;
        },

        getAllItemsRequest: function () {
            var self = this;
            this.params.paging = {notLimits: true};
            var request = this.storage().getData(this.params);

            request.done(function (data) {
                self.setData(data);
            }).fail(this.onError.bind(this));

            return request;
        },
    });
});
