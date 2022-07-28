define([
    'underscore',
    './channel-set-provider',
    './channel-set-processor',
    './date-validator-cache',
    './date-utils',
    './server-time',
    './checkout-config'
], function (
    _,
    channelSetProvider,
    channelSetProcessor,
    dateValidatorCache,
    dateUtils,
    serverTimeModel,
    checkoutConfig
) {
    'use strict';

    var cache = dateValidatorCache.cache,
        millisecondsPerDay = 1000 * 3600 * 24,
        isNeedToCalcPastExcludedDays = true;

    return {
        /**
         * @param {Date|String} date
         * @returns {Boolean}
         */
        isDateRestricted: function (date) {
            return this.restrictDateLessToday(date)
                || this.disableSameDay(date)
                || this.restrictByMinDays(date)
                || this.restrictByMaxDays(date)
                || this.restrictByQuota(date)
                || this.restrictByChannel(date)
                || this.restrictByMinOrderTime(date);
        },

        resetOptions: function () {
            isNeedToCalcPastExcludedDays = true;
        },

        /**
         * Is input date less then today
         * @param {Date} date
         * @returns {Boolean}
         */
        restrictDateLessToday: function (date) {
            var serverTime = serverTimeModel.getServerTime();

            return dateUtils.compareYMD(date, serverTime) === -1;
        },

        /**
         * @param {Date} date
         * @returns {Boolean}
         */
        disableSameDay: function (date) {
            if (dateUtils.isDayEquals(serverTimeModel.getServerTime(), date)) {
                return !this.isSameDayDeliveryAllowed();
            }

            return false;
        },

        /**
         * Is need to restrict day by Quota
         * Is limit for shipping quota of day is not exceeded
         *
         * @param {Date} date
         * @returns {Boolean}
         */
        restrictByQuota: function (date) {
            var day = dateUtils.toISODate(date),
                quota = channelSetProvider.getRestrictedDays();

            return quota[day] === true;
        },

        /**
         * @param {Date} date
         * @returns {Boolean}
         */
        restrictByMinDays: function (date) {
            var minDate;

            this.setMinMaxModifiers(date);
            minDate = this.getMinDate();

            return !!(minDate && dateUtils.compareYMD(date, minDate) === -1);
        },

        /**
         * Get min delivery date by excluded days modifier
         * @return {Date}
         */
        getMinDate: function () {
            var cacheKey = this._getMinCacheKey(),
                min = cache.channelSetConfig.min ? cache.channelSetConfig.min : 0,
                timestamp;

            if (cacheKey !== cache.minDayCacheKey || cache.minDay === null) {
                cache.minDayCacheKey = cacheKey;
                timestamp = +serverTimeModel.getServerTime() + min * millisecondsPerDay;

                cache.minDay = new Date(+timestamp + cache.minModifier * millisecondsPerDay);
            }

            return cache.minDay;
        },

        /**
         * @returns {string}
         * @private
         */
        _getMinCacheKey: function () {
            var modifier = cache.channelSetConfig.min > 0 ? cache.minModifier : 0;

            return cache.channelSetConfig.min + '+' + modifier;
        },

        /**
         * @param {Date} date
         * @returns {Boolean}
         */
        restrictByMaxDays: function (date) {
            var maxDate = this.getMaxDate();

            return !!(maxDate && dateUtils.compareYMD(date, maxDate) >= 0);
        },

        /**
         * Get max delivery date by excluded days modifier
         * @return {Date|boolean}
         */
        getMaxDate: function () {
            var cacheKey = this._getMaxCacheKey(),
                configMaxDate;

            if (cacheKey !== cache.maxDayCacheKey || cache.maxDay === null) {
                cache.maxDay = false;
                cache.maxDayCacheKey = cacheKey;
                configMaxDate = this._getMaxConfigTimestamp();

                if (configMaxDate) {
                    cache.maxDay = new Date(+configMaxDate + cache.maxModifier * millisecondsPerDay);
                }
            }

            return cache.maxDay;
        },

        /**
         * @returns {string}
         * @private
         */
        _getMaxCacheKey: function () {
            var maxModifier = cache.channelSetConfig.max > 0 ? cache.maxModifier : 0;

            return cache.channelSetConfig.max + '+' + maxModifier;
        },

        /**
         * Get min delivery date by channel config settings
         * @return {Number|boolean}
         * @private
         */
        _getMaxConfigTimestamp: function () {
            var channelSetConfig = cache.channelSetConfig,
                max = channelSetConfig.max ? channelSetConfig.max : 0,
                serverDate;

            if (channelSetConfig.max > 0) {
                serverDate = serverTimeModel.getServerTime();

                return +serverDate + max * millisecondsPerDay;
            }

            return false;
        },

        /**
         * @param {Date} date
         * @returns {boolean}
         */
        restrictByChannel: function (date) {
            var dateSchedule = channelSetProcessor.getDateScheduleByDate(date);

            if (!dateSchedule) {
                return true;
            }

            return !+dateSchedule.is_available;
        },

        setChannelSetData: function () {
            if (_.isEmpty(cache.channelSetConfig) || !cache.channelSetConfig.name) {
                dateValidatorCache.resetChannelSetData();
            }
        },

        /**
         * @param {Date} date
         * @returns {boolean}
         */
        restrictByMinOrderTime: function (date) {
            var deliverydateCheckoutConfig = checkoutConfig.getDeliverydateConfig(),
                minOrderTimeInMinutes = deliverydateCheckoutConfig.isBackorder
                    ? cache.channelSetConfig.backorder_time
                    : cache.channelSetConfig.order_time,
                serverTime = serverTimeModel.getServerTime(),
                serverTimeCopy = new Date(serverTime),
                minOrderDate = new Date(serverTimeCopy.setMinutes(
                    serverTimeCopy.getMinutes() + minOrderTimeInMinutes
                ));

            return dateUtils.compareYMD(minOrderDate, date) > 0;
        },

        /**
         * @returns {boolean}
         */
        isSameDayDeliveryAllowed: function () {
            var config = cache.channelSetConfig,
                today;

            if (!config.is_same_day_available
                || (+config.min !== 0 && !_.isNull(config.min) && !_.isUndefined(config.min))
            ) {
                return false;
            }

            today = serverTimeModel.getServerTime();

            return !config.same_day_cutoff || config.same_day_cutoff > today.getHours() * 60 + today.getMinutes();
        },

        /**
         * Set minModifier and maxModifier by excluded days
         * @param {Date} date - from datepicker
         * @returns {void}
         */
        setMinMaxModifiers: function (date) {
            if (!checkoutConfig.getDeliverydateConfig().isOnlyWorkdays
                || (!cache.channelSetConfig.min && !cache.channelSetConfig.max)
            ) {
                return;
            }

            if (isNeedToCalcPastExcludedDays) {
                this.calculateMinMaxModifiersByRange(serverTimeModel.getServerTime(), date);
            }

            this._findMinMax(date);
        },

        /**
         * Find minModifier and maxModifier by excluded days
         * @param {Date} date
         * @private
         * @returns {void}
         */
        _findMinMax: function (date) {
            var minDate,
                maxDate,
                dateSchedule,
                dateString = date.toDateString();

            if (cache.excludedDates.includes(dateString)) {
                return;
            }

            cache.excludedDates.push(dateString);

            maxDate = this.getMaxDate();

            if (maxDate && dateUtils.compareYMD(date, maxDate) === 1) {
                return;
            }

            dateSchedule = channelSetProcessor.getDateScheduleByDate(date);

            if (!dateSchedule || +dateSchedule.is_available) {
                return;
            }

            minDate = this.getMinDate();

            if (cache.channelSetConfig.max) {
                cache.maxModifier++;
            }

            if (minDate && dateUtils.compareYMD(date, minDate) === -1) {
                cache.minModifier++;
            }
        },

        /**
         * Get minModifier and  maxModifier from serverDate to input date
         * @param {Date} calculateFrom
         * @param {Date} calculateTo
         * @returns {void}
         */
        calculateMinMaxModifiersByRange: function (calculateFrom, calculateTo) {
            var currentDate = new Date(+calculateFrom);

            isNeedToCalcPastExcludedDays = false;

            while (dateUtils.compareYMD(calculateTo, currentDate) >= 0) {
                if (!this.restrictDateLessToday(currentDate) && !this.disableSameDay(currentDate)) {
                    this._findMinMax(currentDate);
                }

                currentDate.setDate(currentDate.getDate() + 1);
            }
        }
    };
});
