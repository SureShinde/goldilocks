/**
 * Fix jquery datepicker _checkOffset method for jquery v3.6.0
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (calendar) {
        $.widget('mage.calendar', $.mage.calendar, {

            /** @inheritdoc */
            _create: function () {
                if ($().jquery === '3.6.0') {
                    this.fixDatepicker();
                }

                this._super();
            },

            /**
             * Replace jquery datepicker method
             * @returns {void}
             */
            fixDatepicker: function () {
                $.datepicker._checkOffset = function( inst, offset, isFixed ) {
                    var dpWidth = inst.dpDiv.outerWidth(),
                        dpHeight = inst.dpDiv.outerHeight(),
                        inputWidth = inst.input ? inst.input.outerWidth() : 0,
                        inputHeight = inst.input ? Math.round(inst.input.outerHeight()) : 0, // Amasty fix (just Math.round() outerHeight)
                        viewWidth = document.documentElement.clientWidth + ( isFixed ? 0 : $( document ).scrollLeft() ),
                        viewHeight = document.documentElement.clientHeight + ( isFixed ? 0 : $( document ).scrollTop() );

                    offset.left -= ( this._get( inst, "isRTL" ) ? ( dpWidth - inputWidth ) : 0 );
                    offset.left -= ( isFixed && offset.left === inst.input.offset().left ) ? $( document ).scrollLeft() : 0;
                    offset.top -= ( isFixed && offset.top === ( inst.input.offset().top + inputHeight) ) ? $( document ).scrollTop() : 0;

                    // Now check if datepicker is showing outside window viewport - move to a better place if so.
                    offset.left -= Math.min( offset.left, ( offset.left + dpWidth > viewWidth && viewWidth > dpWidth ) ?
                        Math.abs( offset.left + dpWidth - viewWidth ) : 0 );
                    offset.top -= Math.min( offset.top, ( offset.top + dpHeight > viewHeight && viewHeight > dpHeight ) ?
                        Math.abs( dpHeight + inputHeight ) : 0 );

                    return offset;
                };
            }
        });

        return $.mage.calendar;
    };
});
