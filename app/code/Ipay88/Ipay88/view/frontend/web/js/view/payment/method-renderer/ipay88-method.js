/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Ipay88_Ipay88/payment/ipay88'
            },
            initObservable: function () {
                this._super()
                    .observe([]);
                return this;
            },

            getCode: function () {
                return 'ipay88';
            },
            getConfigPaymentMethods: function() {
                var loadingImage = this.getStoreUrl() + 'pub/media/ipay88/images/ipay88/proccessing.gif';
                var htmlImage = "<img src='"+ loadingImage +"'>";
                jQuery('.ipay88-loading-image').html(htmlImage);

                return jQuery.post(
                    this.getStoreUrl() + 'ipay88/payment/config',
                    {

                    },
                    function (data) {
                        if(data) {
                            var jsonStr = JSON.stringify(data);
                            var jsonText = JSON.parse(jsonStr);
                            jQuery('.ipay88-payment-methods').html(jsonText.wiget);
                            jQuery('.ipay88-loading-image').hide();

                        } else {
                            console.log('fail to load');
                        }
                    }
                );
            },
            getHtmlForm: function() {
                console.log('dmmmm');
                this.getConfigPaymentMethods();
            },
            getStoreUrl: function () {
                return _.map(window.checkoutConfig.storeUrl, function (value, key) {
                    return value;
                });
            },

            getSubmitUrl: function () {
                return this.getStoreUrl() + 'ipay88/index/index';
            },

            getHtml: function () {
                return "Payment via iPay88";
            },

            getValidatedEmailValue: function () {
                return JSON.parse(localStorage['mage-cache-storage'])['checkout-data']['validatedEmailValue'];
            },

            placeOrder: function () {
                jQuery("#frmIpay88").submit();
            },
            /** Returns send check to info */
            getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            getSelectPaymentMethod: function () {
                jQuery('.ipay88_bank_payment_method').on('click', function() {
                    var current = this;

                    jQuery('.ipay88_bank_payment_method')
                        .each(function(){
                            var attr = jQuery(this).attr('data-ischecked');
                            if (typeof attr !== typeof undefined && attr !== false) {
                                // Element has this attribute
                                //jQuery(this).prop('checked','false');
                                jQuery(this).removeAttr('data-ischecked');
                                jQuery(this).removeAttr('checked');
                                jQuery(this).parent('li').removeClass('active');
                            }
                        });

                    jQuery(current).attr('data-ischecked', 1);
                    jQuery(current).attr('checked', 'checked');
                    //jQuery(current).prop('checked','true');

                    jQuery('#ipay88_payment_method_selected').val(jQuery(current).attr('payment_id'));
                    jQuery(this).parent('li').addClass('active');
                });
            },
            loadImageProcessing: function() {
                var loadingImage = this.getStoreUrl() + 'pub/media/ipay88/images/ipay88/proccessing.gif';
                var htmlImage = "<img src='"+ loadingImage +"'>";
                console.log(loadingImage);
                jQuery('.ipay88-loading-image').html(htmlImage);
            }
        });
    }
);
