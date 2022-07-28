var config = {
    config: {
        mixins: {
            'Magento_Ui/js/grid/filters/filters': {
                'Magenest_FbChatbot/js/model/grid/filters-mixin': true
            },
            'Magento_Ui/js/grid/filters/range': {
                'Magenest_FbChatbot/js/model/grid/range-mixin': true
            },
            'Magento_Ui/js/lib/validation/validator': {
                'Magenest_FbChatbot/js/validator-rule-mixin': true
            }
        }
    }
};
