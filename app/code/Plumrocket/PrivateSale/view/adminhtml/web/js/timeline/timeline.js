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
    'Magento_Ui/js/timeline/timeline'
], function (Timeline) {
    'use strict';

    return Timeline.extend({
        defaults: {
            recordTmpl: 'Plumrocket_PrivateSale/timeline/record',
            displayModes: {
                timeline: {
                    template: 'Plumrocket_PrivateSale/timeline/timeline'
                }
            },
        },

        /**
         * Returns start date of provided record.
         *
         * @param {Object} record
         * @returns {String}
         */
        getStartDate: function (record) {
            var startDate =  record['event_from'];

            if (startDate) {
                return startDate;
            }
        },

        /**
         * Returns end date of provided record.
         *
         * @param {Object} record
         * @returns {String}
         */
        getEndDate: function (record) {
            return record['event_to'];
        },

        /**
         * Checks if provided event record is upcoming,
         * i.e. it will start later on.
         *
         * @param {Object} record
         * @returns {Boolean}
         */
        isUpcoming: function (record) {
            return Number(record.status) === 3;
        },

        /**
         * Checks if provided event record is ending soon,
         * i.e. it has already started.
         *
         * @param {Object} record
         * @returns {Boolean}
         */
        isEndingSoon: function (record) {
            return Number(record.status) === 2;
        },

        /**
         * Checks if provided event record is coming soon,
         * i.e. it has already started.
         *
         * @param {Object} record
         * @returns {Boolean}
         */
        isComingSoon: function (record) {
            return Number(record.status) === 5;
        },

        /**
         * Checks if provided event record is disabled,
         * i.e. it has already started.
         *
         * @param {Object} record
         * @returns {Boolean}
         */
        isDisabled: function (record) {
            return Number(record.status) === 0;
        },

        /**
         * Checks if provided event record is ended,
         * i.e. it has already started.
         *
         * @param {Object} record
         * @returns {Boolean}
         */
        isEnded: function (record) {
            return Number(record.status) === 4;
        },
    });
});
