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
    'jquery'
], function ($) {
    'use strict';

    return {
        getElementsDependedOn: function (element) {
            var rows = element.siblings('tr'),
                currentRowIndex = element.index(),
                result = {'toEnable': [], 'toDisable': []},
                elem;

            rows.each(function () {
                elem = $(this);

                if (currentRowIndex < elem.index()) {
                    result.toDisable.push(elem.find('input[type="checkbox"]'));
                } else {
                    result.toEnable.push(elem.find('input[type="checkbox"]'));
                }
            });

            return result;
        }
    };
});
