define([
    'underscore'
], function (
    _
) {
    'use strict';

    /**
     * @param {ChannelSet} channelSet
     * @param {Number} channelId
     * @returns {DeliveryChannel|Object}
     */
    function CreateChannelIndex(channelSet, channelId) {
        /** @type {DeliveryChannel} */
        var channel = _.findWhere(channelSet.channel, { channel_id: channelId });

        return _.extend(channel, {
            _scheduleIds: null,
            _timeIds: null,

            /**
             * @returns {Number[]}
             */
            getDateScheduleIds: function () {
                if (this._scheduleIds === null) {
                    this._scheduleIds = [];
                    channelSet.dateChannelLinks.forEach(function (dateChannelLink) {
                        if (dateChannelLink.delivery_channel_id === this.channel_id) {
                            this._scheduleIds.push(dateChannelLink.date_schedule_id);
                        }
                    }, this);
                }

                return this._scheduleIds;
            },

            getTimeIntervalIds: function () {
                if (this._timeIds === null) {
                    this._timeIds = [];
                    channelSet.timeChannelLinks.forEach(function (timeChannelLink) {
                        if (timeChannelLink.delivery_channel_id === this.channel_id) {
                            this._timeIds.push(timeChannelLink.time_interval_id);
                        }
                    }, this);
                }

                return this._timeIds;
            }
        });
    }

    return CreateChannelIndex;
});
