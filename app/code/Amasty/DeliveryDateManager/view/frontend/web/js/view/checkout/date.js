define([
    'Amasty_DeliveryDateManager/js/view/date',
    'Amasty_DeliveryDateManager/js/model/checkout/date-validator',
    'Amasty_DeliveryDateManager/js/model/checkout/channel-set-provider',
    'Amasty_DeliveryDateManager/js/model/checkout/channel-set-processor'
], function (
    AbstractDate,
    channelSetProvider,
    channelSetProcessor,
    checkoutDateValidator
) {
    'use strict';

    return AbstractDate.extend({
        channelSetProvider: channelSetProvider,
        channelSetProcessor: channelSetProcessor,
        dateValidator: checkoutDateValidator,

        defaults: {
            isRequired: false
        }
    });
});
