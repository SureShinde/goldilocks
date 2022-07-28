/**
 * Element collection which trigger visibility
 */
define([
    'Magento_Ui/js/form/components/fieldset'
], function (
    Component
) {
    'use strict';

    return Component.extend({
        hide: function () {
            this.changeVisibility(false);
        },

        show: function () {
            this.changeVisibility(true);
        },

        initElement: function (elem) {
            this._super(elem);
            elem.visible(this.visible());

            return this;
        },

        /**
         * @param {Boolean} isVisible
         * @returns {void}
         */
        changeVisibility: function (isVisible) {
            this.visible(isVisible);
            this.elems.each(function (element) {
                element.visible(isVisible);
            });
        }
    });
});
