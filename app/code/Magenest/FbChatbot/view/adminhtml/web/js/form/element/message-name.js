define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';
    const MESSAGE_NAME_CANNOT_CHANGE = ['1','2','4','5'];
    return Abstract.extend({
        initialize: function () {
            this._super()
            var cannotChange = MESSAGE_NAME_CANNOT_CHANGE.includes(this.message_id);
            if (cannotChange)
                this.disabled(true);
            return this;
        }
    });
});
