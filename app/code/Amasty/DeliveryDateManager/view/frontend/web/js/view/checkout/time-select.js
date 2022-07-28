define([
    'Amasty_DeliveryDateManager/js/view/time-select',
    'Amasty_DeliveryDateManager/js/model/checkout/channel-set-processor'
], function (
    AbstractTimeSelect,
    channelSetProcessor
) {
    'use strict';

    return AbstractTimeSelect.extend({
        channelSetProcessor: channelSetProcessor
    });
});
