define([
    'underscore',
    'Amasty_DeliveryDateManager/js/model/date-validator',
    'Amasty_DeliveryDateManager/js/model/checkout/channel-set-provider',
    'Amasty_DeliveryDateManager/js/model/checkout/channel-set-processor'
], function (
    _,
    dateValidator,
    checkoutChannelSetProvider,
    checkoutChannelSetProcessor
) {
    'use strict';

    return _.extend(dateValidator, {
        channelSetProvider: checkoutChannelSetProvider,
        channelSetProcessor: checkoutChannelSetProcessor
    });
});
