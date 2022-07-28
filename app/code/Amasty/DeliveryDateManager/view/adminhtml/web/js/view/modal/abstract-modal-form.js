define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/form/form',
    'underscore',
    'mage/translate'
], function ($, uiAlert, uiConfirm, Form, _, $t) {
    'use strict';

    return Form.extend({
        defaults: {
            deleteConfirmationMessage: '',
            notSelectedEntityMessage: '',
            idField: 'id',
            ajaxSettings: {
                method: 'POST',
                dataType: 'json'
            }
        },

        /**
         * Delete entity by provided url.
         * Will call confirmation message to be sure that user is really wants to delete that entity
         *
         * @param {String} url - ajax url
         */
        abstractDelete: function (url) {
            var id = this.source.get('data.' + this.idField);

            if (id) {
                uiConfirm({
                    content: this.deleteConfirmationMessage,
                    actions: {
                        /** @inheritdoc */
                        confirm: function () {
                            this._delete(url);
                        }.bind(this)
                    }
                });
            } else {
                uiAlert({
                    content: this.notSelectedEntityMessage
                });
            }
        },

        /**
         * Perform asynchronous DELETE request to server.
         * @param {String} url - ajax url
         * @returns {Deferred}
         */
        _delete: function (url) {
            var settings = _.extend({}, this.ajaxSettings, {
                    url: url,
                    data: {
                        'form_key': window.FORM_KEY,
                        'id': this.source.get('data.' + this.idField)
                    }
                }),
                that = this;

            $('body').trigger('processStart');

            return $.ajax(settings)
                .done(function (response) {
                    if (response.error) {
                        uiAlert({
                            content: response.message
                        });
                    } else {
                        that.trigger('deleteAction');
                    }
                })
                .fail(function () {
                    uiAlert({
                        content: $t('Sorry, there has been an error processing your request. Please try again later.')
                    });
                })
                .always(function () {
                    $('body').trigger('processStop');
                });
        }
    });
});
