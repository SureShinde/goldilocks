define([
    'Amasty_DeliveryDateManager/js/model/channel-set-provider',
    'Magento_Checkout/js/model/quote'
], function (
    ChannelSetProvider,
    quote
) {
    'use strict';

    ChannelSetProvider._getChannelSet = function () {
        var shippingMethod = quote.shippingMethod();

        if (shippingMethod
            && shippingMethod.extension_attributes
            && shippingMethod.extension_attributes.amdeliverydate_channels
        ) {
            return {
                channel: shippingMethod.extension_attributes.amdeliverydate_channels,
                config: shippingMethod.extension_attributes.amdeliverydate_channel_config,
                dateChannelLinks: shippingMethod.extension_attributes.amdeliverydate_date_channel_links,
                dateScheduleItems: shippingMethod.extension_attributes.amdeliverydate_date_schedule_items,
                timeChannelLinks: shippingMethod.extension_attributes.amdeliverydate_time_channel_links,
                timeScheduleLinks: shippingMethod.extension_attributes.amdeliverydate_time_schedule_links,
                timeIntervalItems: shippingMethod.extension_attributes.amdeliverydate_time_interval_items,
                disabledDaysByLimitItems: shippingMethod.extension_attributes.amdeliverydate_disabled_days_by_limit
            };
        }

        return {
            channel: [],
            config: {},
            dateChannelLinks: [],
            dateScheduleItems: [],
            timeChannelLinks: [],
            timeScheduleLinks: [],
            timeIntervalItems: [],
            disabledDaysByLimitItems: []
        };
    };

    return ChannelSetProvider;
});
