define([
    'Magento_Ui/js/form/element/select'
], function (Select) {
    'use strict';

    return Select.extend({
        defaults: {
            listens: {
                '${ $.provider }:data.clear': 'clear'
            }
        },

        overload: function () {
            this._super();

            this.value.valueHasMutated();

            return this;
        },

        clear: function () {
            this._super();

            this.value.valueHasMutated();

            return this;
        }
    });
});
