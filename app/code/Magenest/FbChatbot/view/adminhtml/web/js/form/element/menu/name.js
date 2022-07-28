define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';
    const MENU_NAME_CANNOT_CHANGE = ['1'];
    return Abstract.extend({
        initialize: function () {
            this._super()
            var cannotChange = MENU_NAME_CANNOT_CHANGE.includes(this.menu_id);
            if (cannotChange)
                this.disabled(true);
            return this;
        }
    });
});
