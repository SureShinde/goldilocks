define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry'
], function (select, uiRegistry) {
    'use strict';
const MESSAGE_SHOW_PRODUCT = "show_product_buttons";
    return select.extend({

        initObservable: function () {
           var message = uiRegistry.get(this.provider).data;
            if(message !== undefined) {
                if (message.code === MESSAGE_SHOW_PRODUCT) {
                    this.disabled = true;
                }
            }
           return this._super();
        },
        onUpdate: function () {
            this.changeVisibility();
        },

        changeVisibility: function () {
            var template = 'ns=' + this.ns +
                ', dataScope=' + this.parentScope +
                ', index=values';
            uiRegistry.async(template)(
                function (currentComponent) {
                    if (currentComponent.recordData().length > 0){
                        currentComponent.reload();
                    }
                }
            );
            return this;
        },

    });
});
