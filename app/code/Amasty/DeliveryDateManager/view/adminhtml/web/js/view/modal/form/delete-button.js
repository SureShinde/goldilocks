define([
    'Magento_Ui/js/form/components/button',
    'jquery'
], function (Button) {
    'use strict';

    return Button.extend({
        defaults: {
            formProvider: '',
            idField: '',
            listens: {
                '${ $.formProvider }:data.${ $.idField }': 'toggleVisible'
            }
        },
        buttonElement: null,

        initialize: function (config, elem) {
            this._super();

            this.buttonElement = jQuery(elem);
            this.buttonElement.on('click', this.action.bind(this));

            return this;
        },

        /**
         * Toggle button visibility
         *
         * @param {String|Number} idValue
         * @return {void}
         */
        toggleVisible: function (idValue) {
            this.buttonElement.toggle(!!parseInt(idValue));
        }
    });
});
