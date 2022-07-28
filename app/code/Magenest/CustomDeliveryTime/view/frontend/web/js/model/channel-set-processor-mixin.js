define([
    'underscore',
    'mage/utils/wrapper',
    'Amasty_DeliveryDateManager/js/model/channel-set-provider',
    'Amasty_DeliveryDateManager/js/model/date-utils',
    'Amasty_DeliveryDateManager/js/model/server-time',
    'Amasty_DeliveryDateManager/js/model/checkout-config'
], function (_,
             wrapper,
             channelSetProvider,
             dateUtils,
             serverTimeModel,
             checkoutConfig) {
    'use strict';

    return function (timeIntervalExcluded) {
        timeIntervalExcluded.isTimeIntervalExcluded = wrapper.wrapSuper(timeIntervalExcluded.isTimeIntervalExcluded, function (date, timeInterval) {
            var channelConfig,
                minOrderTimeInMinutes,
                limitSubtotal,
                minimumDeliveryTime,
                subTotal,
                timeConfig,
                serverTime = serverTimeModel.getServerTime(),
                deliverydateCheckoutConfig = checkoutConfig.getDeliverydateConfig();

            if (!dateUtils.isDayEquals(date, serverTime)) {
                return false;
            }

            channelConfig = channelSetProvider.getChannelSet().config;

            timeConfig = window.checkoutConfig.deliveryconfig.deliverytime;

            limitSubtotal = parseInt(timeConfig.limitSubtotal);

            minimumDeliveryTime = parseInt(timeConfig.minimumDeliveryTime);

            subTotal = parseInt(timeConfig.subTotal);

            if (subTotal > limitSubtotal && limitSubtotal !== 0 && minimumDeliveryTime !== 0) {
                minOrderTimeInMinutes = minimumDeliveryTime * 60 || 0;
            } else {
                minOrderTimeInMinutes = channelConfig.order_time || 0;
                if (deliverydateCheckoutConfig.isBackorder && channelConfig.backorder_time != null) {
                    minOrderTimeInMinutes = channelConfig.backorder_time || 0;
                }
            }

            minOrderTimeInMinutes += serverTime.getHours() * 60 + serverTime.getMinutes();

            if (+timeInterval.to <= minOrderTimeInMinutes
                || +timeInterval.from < minOrderTimeInMinutes
            ) {
                return true;
            }

            return false;
        });

        return timeIntervalExcluded;
    };
});
