define([], function () {
    'use strict';

    return function (targetModule) {
        targetModule.defaults.templates.number = {
            component: 'Magenest_FbChatbot/js/model/form/element/number'
        };

        return targetModule;
    };
});
