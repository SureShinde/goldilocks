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
    'Magento_Ui/js/form/element/select',
], function ($, select) {
    'use strict';

    return select.extend({
        defaults: {
            /**
             * key - values in field 'Restricted Access'
             * value - values in field 'Landing Page'
             */
            dependencyMap: {
                '2': '3',
                '3': '2'
            }
        },

        initObservable: function () {
            return this._super().observe(['disabledOption']);
        },

        getDisabledOption: function () {
            return this.disabledOption();
        },

        isOptionDisabled: function (optionValue) {
            if (! this.dependencyMap.hasOwnProperty(optionValue)) {
                return false;
            }

            return this.dependencyMap[this.getDisabledOption()] === optionValue;
        }
    });
});
