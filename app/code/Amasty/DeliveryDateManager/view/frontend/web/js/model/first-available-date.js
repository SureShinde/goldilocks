/**
 * First available date model
 */

define([
    'underscore',
    './channel-set-provider',
    './server-time',
    './date-validator',
    './date-validator-cache'
], function (
    _,
    channelSetProvider,
    serverTimeModel,
    dateValidator,
    dateValidatorCache
) {
    'use strict';

    const MAX_ITERATIONS_DEFAULT = 180,
        MAX_ITERATIONS = 360;

    var cache = {};

    return {
        /**
         * Get first available delivery date of channel set
         * @param {ChannelSet} channelSet
         * @returns {Date|null}
         */
        getChannelSetFirstAvailableDate: function (channelSet) {
            var dateForIncrement = new Date(serverTimeModel.getServerTime()),
                daysCountToCheck = MAX_ITERATIONS_DEFAULT,
                channelSetCacheKey = channelSetProvider.getChannelSetCacheKey(channelSet),
                firstAvailableDate = this.getFirstAvailableDateFromCache(channelSetCacheKey);

            dateValidatorCache.resetChannelSetData();

            if (!channelSetCacheKey) {
                return null;
            }

            if (!_.isUndefined(firstAvailableDate)) {
                return firstAvailableDate;
            }

            if (channelSet.config.max) {
                daysCountToCheck = Math.min(+channelSet.config.max, MAX_ITERATIONS);
            }

            for (var index = 1; index <= daysCountToCheck; index++) {
                if (!dateValidator.isDateRestricted(dateForIncrement)) {
                    firstAvailableDate = dateForIncrement;

                    break;
                }

                dateForIncrement.setDate(dateForIncrement.getDate() + 1);
            }

            this.setFirstAvailableDateToCache(channelSetCacheKey, firstAvailableDate || null);

            return firstAvailableDate || null;
        },

        /**
         * @param {String} key
         * @param {Date|null} date
         * @returns {void}
         */
        setFirstAvailableDateToCache: function (key, date) {
            cache[key] = date;
        },

        /**
         * @param {String} key
         * @returns {Date|null}
         */
        getFirstAvailableDateFromCache: function (key) {
            return cache[key];
        }
    };
});
