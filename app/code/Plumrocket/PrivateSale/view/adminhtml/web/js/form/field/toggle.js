/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales v4.x.x
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('plum.toggle', {
        _create: function () {
            this._super();
            var input = this.element,
                value;

            this.element = $(this.element);

            this.element.on('click', function () {
                value = input.is(':checked');

                if (! value) {
                    $(this.options.dependency.toDisable).each(function () {
                        $(this).prop('checked', false);
                    });
                } else {
                    $(this.options.dependency.toEnable).each(function () {
                        $(this).prop('checked', true);
                    });
                }

                input.prop('checked', value);
            }.bind(this));
        }
    });

    return $.plum.toggle;
});
