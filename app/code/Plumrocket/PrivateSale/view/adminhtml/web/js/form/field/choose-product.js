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
 * @package     Plumrocket Private Sales and Flash Sales v4.x.x
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

define([
    'Magento_Ui/js/form/element/abstract',
    'uiRegistry',
    'mage/translate'
], function (Abstract, registry, $t) {
    'use strict';

    return Abstract.extend({
        defaults: {
            label: ''
        },

        initObservable: function () {
            this._super().observe(['productLabel', 'productLink', 'productEditUrl']);
            return this;
        },

        changeValue: function () {
            var id = registry.get('prprivatesale_event_product_listing.prprivatesale_event_product_listing.event_product.entity_id').selectedRow(),
                selectedRow = this.getRowById(id);

            registry.get('index = choose_product_button').title($t('Change Product'));
            this.value(id);
            this.productLink(this.productEditUrl() + 'id/' + id);
            this.productLabel(selectedRow.name + ' (' + selectedRow.sku  + ')');
        },

        getRowById: function (id) {
            var rows = registry.get('prprivatesale_event_product_listing.prprivatesale_event_product_listing.event_product').rows;

            return rows.find(function (row) {
                return row['entity_id'] === id;
            });
        },
    });
});
