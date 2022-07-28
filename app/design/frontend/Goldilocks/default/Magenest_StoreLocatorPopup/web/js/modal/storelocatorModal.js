define([
    'jquery',
    'underscore',
    'mage/translate',
    'jquery-ui-modules/widget',
    'Magento_Ui/js/modal/modal'
], function($, _, $t){
    'use strict';
    /**
     * Modal Window Widget
     */
    $.widget('storelocator.locatorpopup', $.mage.modal, {
        /**
         * Close modal.
         * * @return {Element} - current element.
         */
        closeModal: function () {
            var that = this;
            if(typeof $.cookie("nearest_store") == 'undefined') {
                return this.element;
            }
            this._removeKeyListener();
            this.options.isOpen = false;
            this.modal.one(this.options.transitionEvent, function () {
                that._close();
            });
            this.modal.removeClass(this.options.modalVisibleClass);

            if (!this.options.transitionEvent) {
                that._close();
            }

            return this._super();
        },
    });
    return $.storelocator.locatorpopup;
});
