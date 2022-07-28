/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/abstract',
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            elementTmpl: 'Magenest_FbChatbot/form/element/number-input',
        }
    });
});
