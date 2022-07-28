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
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

define([
    'underscore'
], function (_) {
    'use strict';

    function Layout()
    {
        var self = this;
        /**
         * @type {{
         *     homepage: {},
         *     catalog: {},
         * }}
         */
        var layouts = {};

        this.int = function (countdownLayouts) {
            layouts = countdownLayouts;
        };

        /**
         * @param {{type: String, page: String}} options
         * @param {number} timeToEnd
         * @return {string}
         */
        this.getFormat = function (options, timeToEnd) {
            var format = layouts[options.page][options.type];
            if (typeof format === 'string') {
                return format;
            }

            return self.getDynamicFormat(format, timeToEnd);
        };

        /**
         *
         * @param {{default: String, dynamic: {format: String, minTime: number}[]}} format
         * @param timeToEnd
         * @return {string}
         */
        this.getDynamicFormat = function (format, timeToEnd) {
            if (format.dynamic.length) {
                var dynamicFormats = format.dynamic.sort(function (a, b) {
                    if (a.minTime === b.minTime) {
                        return 0;
                    }
                    return a.minTime < b.minTime ? 1 : -1;
                });

                var dynamicFormat = _.find(dynamicFormats, function (format) {
                    return timeToEnd > format.minTime;
                });

                if (dynamicFormat) {
                    return dynamicFormat.format;
                }
            }

            return format.default;
        };
    }

    return new Layout();
});
