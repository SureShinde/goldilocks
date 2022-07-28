define(
    [
        'jquery',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/error-processor'
    ], function (
        $,
        additionalValidators,
        errorProcessor
    ) {
        'use strict';
        var mixin = {
            placeOrder: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate() &&
                    additionalValidators.validate() &&
                    this.isPlaceOrderActionAllowed() === true
                ) {
                    this.isPlaceOrderActionAllowed(false);

                    var form = $("#frmIpay88");
                    var url = form.attr('action');

                    $.ajax({
                        url: url,
                        type: "POST",
                        data: form.serialize(),
                        showLoader: true
                    }).done(function (response) {
                        if (response.success) {
                            var resForm = $(response.htmlForm).appendTo($('[data-container="body"]'));
                            resForm.submit();
                        } else {
                            errorProcessor.process(response, self.messageContainer);
                        }
                    }).fail(function (error) {
                        errorProcessor.process(error, self.messageContainer);
                    }).always(function () {
                        self.isPlaceOrderActionAllowed(true);
                    });

                    return true;
                }

                return false;
            },
        };

        return function (target) {
            return target.extend(mixin);
        };
    });