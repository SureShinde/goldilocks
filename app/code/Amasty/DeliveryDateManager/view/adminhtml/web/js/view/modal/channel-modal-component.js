define([
    'Magento_Ui/js/form/components/insert-form',
    'Magento_Ui/js/modal/alert',
    'uiRegistry'
], function (Insert, uiAlert, registry) {
    'use strict';

    return Insert.extend({
        defaults: {
            targetElementName: '',
            listens: {
                responseData: 'onResponse'
            },
            modules: {
                modalComponent: '${ $.modalProvider }'
            }
        },

        /**
         * Close modal, reload select options
         *
         * @param {Object} responseData
         * @returns {void}
         */
        onResponse: function (responseData) {
            if (responseData.status === 'done') {
                if (responseData.closeAction === true) {
                    this.modalComponent().closeModal();
                } else {
                    this.externalSource().set('data.' + this.externalForm().idField, responseData.id);
                }

                if (this.targetElementName) {
                    registry.get(this.targetElementName, function (target) {
                        target.setExternalValue(responseData.id);
                    });
                }
            } else {
                uiAlert({
                    content: responseData.message
                });
            }
        },

        /**
         * Event method that closes modal and refreshes grid after entity
         * was removed through "Delete" button on the "Edit" modal
         *
         * @returns {void}
         */
        onDelete: function () {
            this.modalComponent().closeModal();

            if (this.targetElementName) {
                registry.get(this.targetElementName, function (target) {
                    target.setExternalValue('');
                });
            }
        },

        /**
         * @param {?Object} params
         * @return {Object|null}
         */
        updateData: function (params) {
            if (!this.isRendered) {
                return null;
            }

            return this._super(params);
        },

        /**
         * Clear external form data
         * @returns {void}
         */
        clearForm: function () {
            if (this.externalSource()) {
                this.externalSource().set('data', {});
                this.externalSource().trigger('data.clear');
                this.externalSource().trigger('data.overload');

                // to reset error on required fields after changing data in provider
                this.externalSource().trigger('data.reset');
            }
        }
    });
});
