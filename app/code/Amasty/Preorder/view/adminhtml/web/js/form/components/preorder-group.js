define([
    'Magento_Ui/js/form/components/group',
    'uiRegistry'
], function (Group, registry) {
    'use strict';

    return Group.extend({
        /**
         * @returns void
         */
        initialize: function () {
            var self = this;

            self._super();

            registry.get('index = backorders', function (backordersComponent) {
                self.show(backordersComponent.value());
            });
        },

        /**
         * @param {Number} value
         * @returns {void}
         */
        show: function (value) {
            this.visible(value == 101);
        }
    });
});
