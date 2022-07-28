/**
 * Server Time Model
 */
define([
    'moment',
    './checkout-config',
    'moment-timezone-with-data'
], function (moment, checkoutConfig) {
    'use strict';

    const SERVER_TIME_UPDATE_DELAY = 60000; // 1 minute

    var cache = {
        serverTime: null,
        serverTimeAsMomentObject: null
    };

    /**
     * @param {String} key
     * @param {Object/Date} date
     * @private
     * @returns {void}
     */
    function _setServerTimeToCache(key, date) {
        cache[key] = date;
    }

    function _resetServerTime() {
        cache.serverTime = null;
        cache.serverTimeAsMomentObject = null;
    }

    function _getServerTimeAsMomentObject() {
        var deliverydateCheckoutConfig = checkoutConfig.getDeliverydateConfig(),
            gmtOffset = deliverydateCheckoutConfig.gmtOffset,
            momentObject = moment().utcOffset(gmtOffset);

        _setServerTimeToCache('serverTimeAsMomentObject', momentObject);

        return momentObject;
    }

    function _getServerTime() {
        var serverTime,
            momentObject = _getServerTimeAsMomentObject();

        serverTime = momentObject.toDate();

        _setServerTimeToCache('serverTime', serverTime);

        return serverTime;
    }

    function _resetServerTimeService() {
        _resetServerTime();
        setTimeout(_resetServerTimeService, SERVER_TIME_UPDATE_DELAY);
    }

    setTimeout(_resetServerTimeService, SERVER_TIME_UPDATE_DELAY);

    return {
        getServerTime: function () {
            return cache.serverTime || _getServerTime();
        },

        getServerTimeAsMomentObject: function () {
            return cache.serverTimeAsMomentObject || _getServerTimeAsMomentObject();
        }
    };
});
