define([
    'underscore',
    './channel-set/channel-indexed',
    './channel-set/channel-set-indexed'
], function (
    _,
    createChannelIndex,
    createChannelSetIndex
) {
    'use strict';

    /**
     * @typedef {Object} ChannelSet
     * @property {DeliveryChannel[]} channel
     * @property {DeliveryConfig} config
     * @property {DateChannelLink[]} dateChannelLinks
     * @property {DateSchedule[]} dateScheduleItems
     * @property {TimeChannelLink[]} timeChannelLinks
     * @property {TimeScheduleLink[]} timeScheduleLinks
     * @property {TimeInterval[]} timeIntervalItems
     * @property {Object[]} disabledDaysByLimit
     */
    /**
     * @typedef {Object} DeliveryChannel
     * @property {Number} channel_id
     * @property {Number} priority
     */
    /**
     * @typedef {Object} DeliveryConfig
     * @property {number} id
     * @property {number|null} min
     * @property {number|null} max
     * @property {bool|null} is_same_day_available
     * @property {number|null} same_day_cutoff
     * @property {number|null} order_time
     * @property {number|null} backorder_time
     */
    /**
     * @typedef {Object} DateChannelLink
     * @property {Number} relation_id
     * @property {Number} delivery_channel_id
     * @property {Number} date_schedule_id
     */
    /**
     * @typedef {Object} DateSchedule
     * @property {Number} schedule_id
     * @property {Number} type
     * @property {string} from - ISO date
     * @property {string} to - ISO date
     * @property {boolean} is_available
     */
    /**
     * @typedef {Object} TimeChannelLink
     * @property {Number} relation_id
     * @property {Number} delivery_channel_id
     * @property {Number} time_interval_id
     */
    /**
     * @typedef {Object} TimeScheduleLink
     * @property {Number} relation_id
     * @property {Number} date_schedule_id
     * @property {Number} time_interval_id
     */
    /**
     * @typedef {Object} TimeInterval
     * @property {Number} interval_id
     * @property {Number} from - minutes
     * @property {Number} to - minutes
     * @property {string} label
     * @property {Number} position
     */

    return {
        _indexedChannelSets: {},
        _indexedDateScheduleByType: [],
        _indexedChannels: {},

        /**
         * @returns {ChannelSet}
         */
        getChannelSet: function () {
            var channelSet = this._getChannelSet(),
                key = this.getChannelSetCacheKey(channelSet);

            if (!this._indexedChannelSets[key]) {
                this._indexedChannelSets[key] = createChannelSetIndex(channelSet);
            }

            return this._indexedChannelSets[key];
        },

        /**
         * @returns {ChannelSet}
         * @private
         */
        _getChannelSet: function () {
            return window.amastyDeliveryDateEditConfig;
        },

        /**
         * @param {ChannelSet} channelSet
         * @returns {String} key
         */
        getChannelSetCacheKey: function (channelSet) {
            var key = '';

            channelSet.channel.forEach(function (channel) {
                key += channel.channel_id;
            }, this);

            return key;
        },

        getIndexedChannels: function () {
            var channelSet = this.getChannelSet(),
                result = [];

            channelSet.channel.forEach(function (channel) {
                if (!this._indexedChannels[channel.channel_id]) {
                    this._indexedChannels[channel.channel_id] = createChannelIndex(channelSet, channel.channel_id);
                }

                result.push(this._indexedChannels[channel.channel_id]);
            }, this);

            return result;
        },

        /**
         * Key is a day in ISO format
         * @returns {Object}
         */
        getRestrictedDays: function () {
            return this.getChannelSet().getRestrictedDays();
        }
    };
});
