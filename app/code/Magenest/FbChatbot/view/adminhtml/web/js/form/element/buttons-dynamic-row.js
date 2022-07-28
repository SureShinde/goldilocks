define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'mage/translate',
    'ko'
], function (Abstract,$t,ko) {
    'use strict';
    return Abstract.extend({
        "defaults": {
            "messageType": ko.observable(0),
            "imports": {
                'updateVisibility': '${ $.provider }:${ $.dataScope }.message_type',
            }
        },
        processingAddChild: function (ctx, index, prop) {
            if (this._elems.length > 3) {
                alert($t('You can only add up to 3 buttons'));
                return false;
            }

            this.showSpinner(true);
            this.addChild(ctx, index, prop);
        },

        updateVisibility: function (value) {
            this.messageType(value);
        }
    });
});
