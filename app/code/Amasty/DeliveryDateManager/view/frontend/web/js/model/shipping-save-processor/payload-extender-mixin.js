define([
    'underscore',
    'uiRegistry',
    'mage/utils/wrapper'
], function (_, registry, wrapper) {
    'use strict';

    return function (payloadExtender) {
        return wrapper.wrap(payloadExtender, function (original, payload) {
            var payloadExtended = original(payload),
                deliverydateData = registry.get('checkoutProvider').get('amdeliverydate'),
                deliveryFieldsetComponent = registry.get({ index: 'amasty-delivery-date' });

            if (_.isUndefined(payloadExtended.addressInformation.extension_attributes)) {
                payloadExtended.addressInformation.extension_attributes = {};
            }

            if (deliverydateData
                && deliveryFieldsetComponent.visible()
                && !deliveryFieldsetComponent.noAvailableDates()
            ) {
                _.extend(payloadExtended.addressInformation.extension_attributes, deliverydateData);
            }

            return payloadExtended;
        });
    };
});
