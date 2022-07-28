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
    'ko',
    'underscore',
    'Magento_Ui/js/grid/tree-massactions',
    'uiRegistry',
    'Plumrocket_PrivateSale/js/grid/data-modifier'
], function (ko, _, Massactions, registry, dataModifier) {
    'use strict';

    return Massactions.extend({
        defaults: {
            template: 'ui/grid/tree-massactions',
            submenuTemplate: 'Plumrocket_PrivateSale/grid/input-submenu',
            inputField: null,
            additionalInfoVisibility: false,
            listens: {
                opened: 'hideSubmenus'
            }
        },

        initObservable: function () {
            return this._super().observe('additionalInfoVisibility');
        },

        setValue: function (actionIndex, action, event) {
            this.getAction(actionIndex).value = event.target.value;
        },

        toggleAdditionalInfo: function () {
            this.additionalInfoVisibility(! this.additionalInfoVisibility());
        },

        /**
         * Applies specified action.
         *
         * @param {String} actionIndex - Actions' identifier.
         * @returns {Massactions} Chainable.
         */
        applyAction: function (actionIndex) {
            var action = this.getAction(actionIndex),
                visibility,
                inputElements;

            if (action.visible) {
                visibility = action.visible();

                this.hideSubmenus(action.parent);
                action.visible(!visibility);

                return this;
            }

            inputElements = document.querySelectorAll('input[data-part="' + actionIndex + '"]');

            for (var input of inputElements) {
                if (! Validation.validate(input)) {
                    return this;
                }
            }

            return this._super(actionIndex);
        },

        /**
         * Default action callback. Sends selections data
         * via POST request.
         *
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        defaultCallback: function (action, data) {
            if (data.excludeMode) {
                var request = registry.get(this.provider).getAllItemsRequest();

                request.done(function (response) {
                    this.reloadDataInProvider(action, data);
                }.bind(this));
            } else {
                this.reloadDataInProvider(action, data);
            }

            return this;
        },

        reloadDataInProvider: function (action, data) {
            var sourceData = this.source.data,
                itemsType = data.excludeMode ? 'excluded' : 'selected',
                columnIndex = action.type.split('.')[0],
                actionData = this.getAction(columnIndex),
                isInclude = false,
                changedItems = {};

            for (var item of sourceData.items) {
                isInclude = data[itemsType].includes(item.entity_id);

                if ((data.excludeMode && ! isInclude) || (! data.excludeMode && isInclude)) {
                    item[columnIndex] = action.value;

                    if (actionData.columnActions) {
                        for (var columnAction of actionData.columnActions) {
                            item[columnAction.dep]
                                = dataModifier.execute(columnAction.callback, [item, action.value])[columnAction.dep];
                        }
                    }

                    changedItems[item.entity_id] = item;
                }
            }

            registry.get('prprivatesale_discount_listing.prprivatesale_discount_listing.privatesale_discount').set('rows', sourceData.items);
            registry.get(this.provider).bindItems(changedItems);

            return this;
        }
    });
});
