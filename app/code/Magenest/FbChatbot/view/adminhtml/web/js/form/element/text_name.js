define([
    'Magento_Ui/js/form/element/textarea',
], function (select) {
    'use strict';
    return select.extend({
        "defaults": {
            "imports": {
                'updateVisibility': '${ $.provider }:${ $.parentScope }.message_type',
            },
        },
        updateVisibility: function (value) {
            switch (value) {
                case 3:
                case 8:
                    this.show();
                    break;
                default:
                    this.hide();
                    break;
            }
        }
    });
});
