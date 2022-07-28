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
    'jquery',
    'uiElement',
    'uiRegistry',
    'Magento_Ui/js/modal/alert'
], function ($, Element, registry, alert) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'Plumrocket_PrivateSale/grid/import-button',
            templates: {
                'input': 'Plumrocket_PrivateSale/grid/import/input',
                'select': 'Plumrocket_PrivateSale/grid/import/select'
            },
        },

        /**
         * Compose params object that will be added to request.
         *
         * @returns {Object}
         */
        getParams: function () {
            var fields = $('[data-part="import"]'),
                formData = new FormData();

            for (var inputField of fields) {
                if (! Validation.validate(inputField)) {
                    return false;
                }
            }

            formData.append('form_key', window.FORM_KEY);

            for (var field of fields) {
                formData.append(field.name, this.getFieldVallue(field));
            }

            return formData;
        },

        getFieldVallue: function (field) {
            switch (field.type) {
                case 'file':
                    return field.files[0];
                case 'checkbox':
                    return field.checked;
                default:
                    return field.value;
            }
        },

        /**
         * Redirect to builded url.
         */
        applyOption: function () {
            var self = this, params = this.getParams();

            if (! params) {
                return;
            }

            $('body').trigger('processStart');

            $.ajax({
                url: this.submitUrl,
                data: this.getParams(),
                cache: false,
                contentType : false,
                processData : false,
                success: function () {
                    registry.get(self.provider).set('params.t', new Date().getTime());
                }
            }).done(function () {
                $('body').trigger('processStop');
            }).error(function (response) {
                response = response.responseJSON;
                alert({content: response.message});
                $('body').trigger('processStop');
            });
        },

        getTemplateByType: function (type) {
            if (this.templates[type] !== undefined) {
                return this.templates[type]
            }

            return this.templates['input'];
        },

        getAdditionalAttributes: function (additionalAttributes, type) {
            return additionalAttributes[type];
        }
    });
});
