define([
    'Magento_Checkout/js/view/summary/abstract-total'
], function (viewModel) {
    'use strict';

    return viewModel.extend({
        defaults: {
            displayArea: 'after_details',
            template: 'Amasty_Preorder/summary/item/details/preorder_note'
        },

        /**
         * @param {Object} quoteItem
         * @return {*|String}
         */
        getValue: function (quoteItem) {
            var result = '';

            if (quoteItem['extension_attributes']
                && quoteItem['extension_attributes']['preorder_info']
                && quoteItem['extension_attributes']['preorder_info']['note']
            ) {
                result = quoteItem['extension_attributes']['preorder_info']['note'];
            }

            return result;
        }
    });
});
