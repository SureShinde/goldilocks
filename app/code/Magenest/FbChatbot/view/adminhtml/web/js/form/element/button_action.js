define([
    './select',
    'underscore',
    'uiRegistry',
    'ko',
    'mage/translate'
], function (select, _, uiRegistry, ko, $t) {
    'use strict';

    var textValue;

    const MESSAGE_TYPE_PRODUCT = 1;
    const MESSAGE_TYPE_CATEGORIES = 2;
    const MESSAGE_TYPE_TEXT = 3;
    const MESSAGE_TYPE_ORDER = 6;
    const MESSAGE_TYPE_TEXT_IMAGE = 8;
    const MESSAGE_TYPE_WISHLIST = 7;
    const MESSAGE_TYPE_CANCEL_ORDER_CREATION = 10;
    const BUTTON_SHOW_PRODUCTS = 1;
    const BUTTON_VIEW_PRODUCT_DETAIL = 2;
    const BUTTON_VIEW_CATEGORY_DETAIL = 3;
    const BUTTON_ADD_TO_CART = 4;
    const BUTTON_MODIFY_AND_CHECKOUT = 5;
    const BUTTON_WRITE_A_PRODUCT_REVIEW = 6;
    const BUTTON_VIEW_ORDER_DETAIL = 7;

    const BUTTON_SHOW_NEXT_MESSAGE = 1;
    const BUTTON_SHOW_URL = 2;
    const BUTTON_SHOW_PHONE_NUMBER = 3;
    const BUTTON_SHOW_ACTION = 4;

    function inArray(needle, haystack) {
        var length = haystack.length;
        for (var i = 0; i < length; i++) {
            if (haystack[i] == needle) return true;
        }
        return false;
    }

    return select.extend({
        "defaults": {
            "imports": {
                'hideOptions': '${ $.parentName }:parent',
                'updateVisibility': '${ $.provider }:${ $.parentScope }.button_type'
            }
        },

        initialize: function () {
            this._super();
            if (this.componentType === 'form.select'){
                this.initialValue = '';
            }
            return this;
        },

        initObservable: function () {
            this._super().observe('elementTmpl', 'notice');
            if (typeof this.options()[0] !== undefined){
                this.messageOptions = this.options()[0];
            }
            if (typeof this.options()[1] !== undefined){
                this.actionOptions = ko.observable(this.options()[1]);
            }
            return this;
        },

        hideOptions: function (value) {
            if (typeof uiRegistry.get(value).messageType() !== undefined) {
                this.filters(uiRegistry.get(value).messageType());
            }
            return this;
        },

        filters: function (value) {
            var source = this.actionOptions(),
                result = [];
            _.filter(source, function (item) {
                switch (value) {
                    case MESSAGE_TYPE_PRODUCT:
                    case MESSAGE_TYPE_WISHLIST:
                        if (inArray(item.value, [BUTTON_VIEW_PRODUCT_DETAIL, BUTTON_ADD_TO_CART, BUTTON_WRITE_A_PRODUCT_REVIEW])) {
                            result.push(item);
                        }
                        break;
                    case MESSAGE_TYPE_CATEGORIES:
                        if (inArray(item.value, [BUTTON_SHOW_PRODUCTS, BUTTON_VIEW_CATEGORY_DETAIL, BUTTON_MODIFY_AND_CHECKOUT])) {
                            result.push(item);
                        }
                        break;
                    case MESSAGE_TYPE_TEXT:
                    case MESSAGE_TYPE_TEXT_IMAGE:
                    case MESSAGE_TYPE_CANCEL_ORDER_CREATION:
                        if (inArray(item.value, [BUTTON_MODIFY_AND_CHECKOUT])) {
                            result.push(item);
                        }
                        break;
                    case MESSAGE_TYPE_ORDER:
                        if (inArray(item.value, [BUTTON_WRITE_A_PRODUCT_REVIEW, BUTTON_VIEW_ORDER_DETAIL])) {
                            result.push(item);
                        }
                        break;

                }
            });
            this.actionOptions(result);
        },

        updateVisibility: function (value) {
            this.validation = {'required-entry': true};
            switch (value) {
                case BUTTON_SHOW_NEXT_MESSAGE:
                    if (this.valueChangedByUser) {
                        textValue = this.value();
                    }
                    this.elementTmpl('ui/form/element/select');
                    this.componentType = 'form.select';
                    this.setOptions(this.messageOptions);
                    this.notice($t('Select a message name'));
                    break;
                case BUTTON_SHOW_ACTION:
                    if (this.valueChangedByUser) {
                        textValue = this.value();
                    }
                    this.elementTmpl('ui/form/element/select');
                    this.componentType = 'form.select';
                    this.setOptions(this.actionOptions());
                    this.notice($t('Select a action'));
                    break;
                default:
                    this.elementTmpl('ui/form/element/input');
                    this.componentType = 'form.input';
                    if (this.valueChangedByUser) {
                        this.setOptions(textValue);
                    } else {
                        this.setOptions(this.initialValue);
                    }
                    if (value === 3) {
                        this.notice($t('Insert a phone number or {$storeTelephone} here. Format must have "+" prefix followed by the country code. For example +84 987681234'));
                        this.validation['mobileCustom'] = true;
                    } else {
                        this.notice($t('Insert a link or {$baseUrl} to get store URL here '));
                        this.validation['validate-url-custom'] = true;
                    }
                    break;
            }
        }
    });
});
