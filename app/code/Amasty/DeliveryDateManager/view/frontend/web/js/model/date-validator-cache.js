/**
 * Date validator cache model
 */

define([
    'underscore',
    './channel-set-provider'
], function (
    _,
    channelSetProvider
) {
    'use strict';

    var cache = {
        /**
         * @type {Date|boolean|null}
         */
        minDay: null,

        /**
         * @type {Date|boolean|null}
         */
        maxDay: null,

        minDayCacheKey: null,
        maxDayCacheKey: null,

        /**
         * @type {DeliveryConfig}
         */
        channelSetConfig: {},

        minModifier: 0,
        maxModifier: 0,
        excludedDates: []
    };

    return {
        cache: cache,

        resetModifiersCache: function () {
            cache.minModifier = 0;
            cache.maxModifier = 0;
            cache.minDayCacheKey = null;
            cache.maxDayCacheKey = null;
            cache.excludedDates = [];
        },

        resetCachedData: function () {
            this.resetChannelSetData();
            this.resetModifiersCache();
        },

        resetChannelSetData: function () {
            cache.channelSetConfig = channelSetProvider.getChannelSet().config;
        }
    };
});
