define([
    'jquery',
    'underscore',
    'Amasty_DeliveryDateManager/js/view/links-grid-row/links-grid-cell/elements/cell-actions/button',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($j, _, ButtonElement, uiAlert, $t) {
    'use strict';

    return ButtonElement.extend({
        defaults: {
            duplicateUrl: '',
            modalType: ''
        },

        /**
         * Performs configured actions
         * @returns {void}
         */
        action: function () {
            this.duplicate()
                .done(function (response) {
                    if (response.error) {
                        uiAlert({
                            content: response.message
                        });

                        return;
                    }

                    this.selectValue = response.id;
                    this.actions.forEach(this.applyAction, this);
                }.bind(this))
                .fail(function () {
                    uiAlert({
                        content: $t('Sorry, there has been an error processing your request. Please try again later.')
                    });
                })
                .always(function () {
                    $j('body').trigger('processStop');
                });
        },

        /**
         * @param {?Object} params
         * @returns {Object}
         */
        duplicate: function (params) {
            var settings = _.extend({}, params, {
                url: this.duplicateUrl,
                data: {
                    'form_key': window.FORM_KEY,
                    'id': this.selectValue,
                    'type': this.modalType
                }
            });

            return $j.ajax(settings);
        }
    });
});
