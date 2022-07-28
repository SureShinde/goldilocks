define([
    'underscore',
    './channel-set-provider',
    './date-utils',
    './server-time',
    './checkout-config'
], function (
    _,
    channelSetProvider,
    dateUtils,
    serverTimeModel,
    checkoutConfig
) {
    'use strict';

    const DATE_SCHEDULE_TYPES = {
        STRICT: 0,
        DAY_OF_YEAR: 1,
        DAY_OF_MONTH: 2,
        DAY_OF_WEEK: 3
    };

    return {
        /**
         * Returns actual time intervals
         * @param {Date} date
         * @returns {TimeInterval[]}
         */
        getTimeIntervalsByDate: function (date) {
            var dateSchedule = this.getDateScheduleByDate(date),
                channelSet,
                timeIds,
                dateISO,
                resultTimeIntervals = [];

            if (_.isUndefined(dateSchedule)) {
                return resultTimeIntervals;
            }

            timeIds = this.getTimeIdsByDateScheduleId(dateSchedule.schedule_id);

            if (!timeIds.length) {
                timeIds = this.getTimeIdsFromChannels();
            }

            channelSet = channelSetProvider.getChannelSet();
            dateISO = dateUtils.toISODate(date);

            channelSet.timeIntervalItems.forEach(function (timeInterval) {
                if (timeIds.includes(timeInterval.interval_id)
                    && this.isTimeIntervalEnabled(dateISO, timeInterval)
                    && !this.isTimeIntervalExcluded(date, timeInterval)
                ) {
                    resultTimeIntervals.push(timeInterval);
                }
            }, this);

            return resultTimeIntervals;
        },

        /**
         * @param {Number} dateScheduleId
         * @returns {Number[]}
         */
        getTimeIdsByDateScheduleId: function (dateScheduleId) {
            var timeIds = [];

            channelSetProvider.getChannelSet().timeScheduleLinks.forEach(function (dateChannelLink) {
                if (dateScheduleId === dateChannelLink.date_schedule_id) {
                    timeIds.push(dateChannelLink.time_interval_id);
                }
            });

            return timeIds;
        },

        /**
         * @returns {Number[]}
         */
        getTimeIdsFromChannels: function () {
            var timeIds = [];

            _.find(channelSetProvider.getIndexedChannels(), function (channel) {
                timeIds = channel.getTimeIntervalIds();

                return timeIds.length;
            }, this);

            return timeIds;
        },

        /**
         * @param {Date} date
         * @returns {DateSchedule|undefined}
         */
        getDateScheduleByDate: function (date) {
            var deliveryChannelsIndexed = channelSetProvider.getIndexedChannels(),
                dateSchedules = channelSetProvider.getChannelSet().dateScheduleItems,
                result;

            _.find(deliveryChannelsIndexed, function (channel) {
                var dateIds = channel.getDateScheduleIds();

                result = _.find(dateSchedules, function (dateSchedule) {
                    return dateIds.includes(dateSchedule.schedule_id)
                        && this.isDateInDateSchedule(date, dateSchedule);
                }, this);

                return result;
            }, this);

            return result;
        },

        /**
         * @param {Date} date
         * @param {DateSchedule} dateSchedule
         * @returns {Boolean}
         */
        isDateInDateSchedule: function (date, dateSchedule) {
            var input = this.convertToComparableByType(date, dateSchedule.type),
                from = this.convertToComparableByType(new Date(dateSchedule.from), dateSchedule.type),
                to = this.convertToComparableByType(new Date(dateSchedule.to), dateSchedule.type);

            if (+dateSchedule.type === DATE_SCHEDULE_TYPES.DAY_OF_WEEK) {
                if (from > to) {
                    return input >= from || input <= to;
                }

                return from <= input && to >= input;
            }

            // Situation when range start is "end" of the week, but range end is the "start" of the week
            // For example: from 25 to 1
            if (+dateSchedule.type !== DATE_SCHEDULE_TYPES.STRICT && dateUtils.compareYMD(from, to) > 0) {
                return dateUtils.compareYMD(input, from) >= 0 || dateUtils.compareYMD(to, input) >= 0;
            }

            return dateUtils.compareYMD(input, from) >= 0 && dateUtils.compareYMD(to, input) >= 0;
        },

        /**
         * @param {Date} date
         * @param {Number} type
         *
         * @returns {Date|Number}
         */
        convertToComparableByType: function (date, type) {
            var result;

            switch (+type) {
                case DATE_SCHEDULE_TYPES.DAY_OF_YEAR:
                    return new Date(1970, date.getMonth(), date.getDate());
                case DATE_SCHEDULE_TYPES.DAY_OF_MONTH:
                    return new Date(1970, 0, date.getDate());
                case DATE_SCHEDULE_TYPES.DAY_OF_WEEK:
                    result = date.getDay();

                    if (result === 0) {
                        result = 7;
                    }

                    return result;
                default:
                    break;
            }

            return date;
        },

        /**
         * @param {String} date
         * @param {TimeInterval} timeInterval
         * @returns {Boolean}
         */
        isTimeIntervalEnabled: function (date, timeInterval) {
            var quota = channelSetProvider.getRestrictedDays(),
                result = true;

            if (_.isArray(quota[date])) {
                _.find(quota[date], function (disabledInterval) {
                    if (disabledInterval.from === timeInterval.from
                        && disabledInterval.to === timeInterval.to
                    ) {
                        result = false;

                        return true;
                    }

                    return false;
                });
            }

            return result;
        },

        /**
         * @param {Date} date
         * @param {TimeInterval} timeInterval
         * @returns {Boolean}
         */
        isTimeIntervalExcluded: function (date, timeInterval) {
            var channelConfig,
                minOrderTimeInMinutes,
                serverTime = serverTimeModel.getServerTime(),
                deliverydateCheckoutConfig = checkoutConfig.getDeliverydateConfig();

            if (!dateUtils.isDayEquals(date, serverTime)) {
                return false;
            }

            channelConfig = channelSetProvider.getChannelSet().config;
            minOrderTimeInMinutes = channelConfig.order_time || 0;

            if (deliverydateCheckoutConfig.isBackorder && channelConfig.backorder_time != null) {
                minOrderTimeInMinutes = channelConfig.backorder_time || 0;
            }

            minOrderTimeInMinutes += serverTime.getHours() * 60 + serverTime.getMinutes();

            if (+timeInterval.to <= minOrderTimeInMinutes
                || +timeInterval.from < minOrderTimeInMinutes
            ) {
                return true;
            }

            return false;
        }
    };
});
