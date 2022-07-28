/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/full-screen-loader',
    'ko',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Customer/js/model/customer',
], function ($, Component, quote, stepNavigator, storage, urlBuilder, fullScreenLoader, ko, getTotalsAction,customer) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magenest_SpecialCustomerProgram/special-customer-program',
            ci_number: window.checkoutConfig.quoteData.ci_number,
            ci_full_name: window.checkoutConfig.quoteData.ci_full_name,
            ci_image: window.checkoutConfig.quoteData.ci_image,
            ci_image_name: ko.observable(''),
            ci_image_src: window.checkoutConfig.ci_image_src,
            isApplied: ko.observable(''),
            delete: ko.observable(''),
            validateImage: ko.observable(''),
        },
        initialize: function () {
            this._super();
            var self = this;
            self.isApplied(window.checkoutConfig.quoteData.special_customer_program === '1');
        },
        isVisible: function () {
            return !stepNavigator.isProcessed('shipping');
        },
        submit: function () {
            if (this.validate()) {
                var serviceUrl, self = this,
                    payload;
                payload = {
                    param: {
                        ci_number: self.ci_number,
                        ci_full_name: self.ci_full_name,
                        ci_image: self.ci_image_name(),
                        cartId: quote.getQuoteId(),
                    },
                };
                fullScreenLoader.startLoader();
                if (!customer.isLoggedIn()) {
                    serviceUrl = urlBuilder.createUrl('/guest-specialCustomerProgram/mine/set-ci-information', {});
                }else {
                    serviceUrl = urlBuilder.createUrl('/specialCustomerProgram/mine/set-ci-information', {});
                }
                return storage.post(
                    serviceUrl, JSON.stringify(payload), false
                ).done(function (result) {
                    self.isApplied(true);
                    fullScreenLoader.stopLoader();
                }).fail(function (response) {
                    console.log(response);
                    fullScreenLoader.stopLoader();
                });
            }
        },
        remove: function () {
            var serviceUrl, self = this,
                payload;
            payload = {
                param: {
                    cartId: quote.getQuoteId(),
                },
            };
            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-specialCustomerProgram/mine/remove-ci-information', {});
            }else {
                serviceUrl = urlBuilder.createUrl('/specialCustomerProgram/mine/remove-ci-information', {});
            }
            fullScreenLoader.startLoader();
            return storage.post(
                serviceUrl, JSON.stringify(payload), false
            ).done(function (result) {
                self.isApplied(false);
                $("#previewImage").attr('src', '');
                $("#form-special-customer-program").trigger('reset');
                self.ci_number = '';
                self.ci_full_name = '';
                self.ci_image_src = '';
                // The cart page totals summary block update
                var deferred = $.Deferred();
                getTotalsAction([], deferred);
                fullScreenLoader.stopLoader();
            }).fail(function (response) {
                console.log(response);
                fullScreenLoader.stopLoader();
            });
        },
        validate: function () {
            var self = this;
            if ($("#ci_image").val() === "") {
                self.validateImage(true);
            } else {
                self.validateImage(false);
            }
            var form = '#form-special-customer-program';
            return $(form).validation() && $(form).validation('isValid') && !self.validateImage();
        },
        deleteImage: function () {
            var self = this;
            if (!self.isApplied()) {
                $("#ci_image").val("");
                $("#previewImage").attr('src', '');
                self.delete(true);
            }
        },
        selectImage: function () {
            var self = this;
            if (!self.isApplied()) {
                if (!self.delete()) {
                    $("#ci_image").trigger('click');
                }
                self.delete(false);
            }
        },
        changeImage: function () {
            var self = this;
            var url = window.checkoutConfig.uploadImageUrl;
            var fileInput = document.getElementById('ci_image');
            var formKey = window.checkoutConfig.formKey;
            var formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('form_key', formKey);
            self.ci_image_name(fileInput.files[0].name);
            $.ajax({
                showLoader: true,
                url: url,
                data: formData,
                type: "POST",
                dataType: 'json',
                processData: false,
                contentType: false,
                cache: false
            }).done(function (data) {
                self.ci_image_name(data.correct);
            });
        }
    });
});
