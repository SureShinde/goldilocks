var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
                'Amasty_DeliveryDateManager/js/model/shipping-save-processor/payload-extender-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Amasty_DeliveryDateManager/js/view/shipping-mixin': true
            }
        }
    },
    'shim': {
        'Amasty_DeliveryDateManager/js/view/time-select': [ 'Amasty_DeliveryDateManager/js/lib/storage-section' ],
        'Amasty_DeliveryDateManager/js/view/fieldset': [ 'Amasty_DeliveryDateManager/js/lib/storage-section' ],
        'Amasty_DeliveryDateManager/js/view/date': [ 'Amasty_DeliveryDateManager/js/lib/storage-section' ]
    }
};
