define([
    'jquery'
], function ($) {
    'use strict';

    return {
        /**
         * @param {String} cssClass
         * @param {Boolean} state
         * @returns {void}
         */
        toggleCustomCssClass: function (cssClass, state) {
            $('#ui-datepicker-div').toggleClass(cssClass, state);
        }
    };
});
