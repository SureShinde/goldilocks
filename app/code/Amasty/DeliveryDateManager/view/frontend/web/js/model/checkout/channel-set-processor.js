define([
    'underscore',
    'Amasty_DeliveryDateManager/js/model/channel-set-processor',
    './channel-set-provider'
], function (
    _,
    AbstractChannelSetProcessor,
    CheckoutChannelSetProvider

) {
    'use strict';

    return _.extend(AbstractChannelSetProcessor, {
        channelSetProvider: CheckoutChannelSetProvider
    });
});
