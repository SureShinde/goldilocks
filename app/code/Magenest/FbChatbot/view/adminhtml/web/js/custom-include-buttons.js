/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/single-checkbox'
], function ($,_,registry,Element) {
    'use strict';

    const MESSAGE_TYPE_PRODUCT_DISPLAY  = 1;
    const MESSAGE_TYPE_CATEGORY_DISPLAY = 2;
    const MESSAGE_TYPE_ORDER_DISPLAY    = 6;
    const MESSAGE_TYPE_WISHLIST_DISPLAY = 7;
    const MESSAGE_TYPE_CREATE_ORDER     = 9;
    const MESSAGE_TYPE_CANCEL_ORDER_CREATION = 10;

    return Element.extend({
        defaults: {
            imports: {
                updateVisibility: '${ $.provider }:${ $.parentScope }.message_type',
            }
        },
        /**
         * Initialize component.
         * @returns {Element}
         */
        initialize: function () {
            return this
                ._super()
                .onCheckedChanged(this.checked());
        },

        /**
         * Change visibility for samplesFieldset & linksFieldset based on current statuses of checkbox.
         */
        changeVisibility: function (newChecked) {
            var template = 'ns=' + this.ns +
                ', dataScope=' + this.parentScope +
                ', index=values',
                visible = (newChecked) ? newChecked : false;

            registry.async(template)(
                function (currentComponent) {
                    currentComponent.visible(visible);
                }
            );
            return this;
        },
        /**
         * Handle checked state changes for checkbox / radio button.
         *
         * @param {Boolean} newChecked
         */
        onCheckedChanged: function (newChecked) {
            this.changeVisibility(newChecked);
            this._super();
        },

        updateVisibility: function (value) {
            switch (value) {
                case MESSAGE_TYPE_PRODUCT_DISPLAY:
                case MESSAGE_TYPE_CATEGORY_DISPLAY:
                case MESSAGE_TYPE_ORDER_DISPLAY:
                case MESSAGE_TYPE_WISHLIST_DISPLAY:
                    this.disabled(true);
                    this.checked(true);
                    break;
                case MESSAGE_TYPE_CREATE_ORDER:
                case MESSAGE_TYPE_CANCEL_ORDER_CREATION:
                    this.disabled(true);
                    this.checked(false);
                    break;
                default:
                    this.disabled(false);
                    break;
            }
        }

    });
});
