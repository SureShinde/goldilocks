define([
    'Magento_Ui/js/form/components/button',
    'mageUtils'
], function (Element, utils) {
    'use strict';

    return Element.extend({
        defaults: {
            ignoreTmpls: {
                actions: true
            }
        },

        applyAction: function (action) {
            var newAction = utils.template(action, this);

            this._super(newAction);
        },

        /**
         * @param {String} inputValue
         * @return {void}
         */
        toggleDisabled: function (inputValue) {
            this.disabled(!(!!+inputValue));
        }
    });
});
