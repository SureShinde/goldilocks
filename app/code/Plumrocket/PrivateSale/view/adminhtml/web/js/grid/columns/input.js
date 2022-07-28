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
    'uiRegistry',
    'Magento_Ui/js/grid/columns/column',
    'Plumrocket_PrivateSale/js/grid/data-modifier'
], function (registry, column, dataModifier) {
    'use strict';

    return column.extend({
        defaults: {
            bodyTmpl: 'Plumrocket_PrivateSale/grid/cells/input'
        },

        updateRow: function (uiClass, event, row) {
            row[uiClass.index] = event.currentTarget.value;

            if (uiClass.dep) {
                row[uiClass.dep] = uiClass.callback
                    ? dataModifier.execute(uiClass.callback, [row])[uiClass.dep]
                    : event.currentTarget.value;

                document.querySelector('[data-index=' + uiClass.dep + '_' + row.entity_id + ']').value
                    = row[uiClass.dep];
            }

            registry.get(uiClass.provider).bindItems({id: row});
            return this;
        }
    });
});
