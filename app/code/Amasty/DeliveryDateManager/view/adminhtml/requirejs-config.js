var config = {
    config: {
        mixins: {
            'Magento_Ui/js/lib/validation/validator': {
                'Amasty_DeliveryDateManager/js/validation-rules': true
            },
            'mage/utils/misc': {
                'Amasty_DeliveryDateManager/js/utils/misc-mixin': true
            },
            'mage/calendar': {
                'Amasty_DeliveryDateManager/js/view/widget/datepicker-mixin': true
            }
        }
    },
    map: {
        '*': {
            'amdeliveryTimepicker': 'Amasty_DeliveryDateManager/js/view/widget/timepicker'
        }
    }
};
