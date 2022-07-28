define([
    'underscore'
], function (
    _
) {
    'use strict';

    /**
     * @param {ChannelSet} channelSet
     * @returns {ChannelSet|Object}
     */
    function CreateChannelSetIndex(channelSet) {
        return _.extend(channelSet, {
            /**
             * Key is a day in ISO format
             * @returns {Object}
             */
            getRestrictedDays: function () {
                if (_.isUndefined(this.disabledDaysByLimit)) {
                    this.disabledDaysByLimit = {};

                    _.each(this.disabledDaysByLimitItems, function (day) {
                        this.disabledDaysByLimit[day.day] = !day.intervals ? true : day.intervals;
                    }, this);
                }

                return this.disabledDaysByLimit;
            }
        });
    }

    return CreateChannelSetIndex;
});
