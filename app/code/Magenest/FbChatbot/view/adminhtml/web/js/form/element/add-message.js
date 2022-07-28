define([
    'Magento_Ui/js/form/components/button',
    'uiRegistry'
], function (button, uiRegistry) {
    'use strict';
    const MESSAGE_SHOW_PRODUCT = "show_product_buttons";
    return button.extend({

        initObservable: function () {
            var message = uiRegistry.get(this.provider).data;
            if(message !== undefined) {
                if (message.code === MESSAGE_SHOW_PRODUCT) {
                    this.disabled = true;
                }
            }

            return this._super();
        }
    });
});
