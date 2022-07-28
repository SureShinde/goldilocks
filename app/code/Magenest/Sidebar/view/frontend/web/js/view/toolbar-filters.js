define([
    "jquery",
    "ko",
    "uiClass",
    "uiComponent",
    'mage/translate'
], function ($, ko, Class, Component,$t) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Magenest_Sidebar/view/toolbar-filters'
        },

        initialize: function () {
            this._super();
            let i = 0, options = [];
            for (const option in this.data.options) {
                let code = option,
                    value = this.data.options[code];
                options.push({'label':$t(value),'attribute_code': code});
                i++;
            }
            this.data.options = options;
            this.data.optionAll = {'label':$t('All'),'attribute_code': 'allToolbarAttributes'};
        },
        isActive: function (attribute_code) {
            var product_list_order = this.getUrlParameter('product_list_order');
            var product_list_dir = this.getUrlParameter('product_list_dir');
            return  attribute_code === product_list_order + '_' +product_list_dir;
        },
        buildHref: function (paramValue) {
            var sortDir = paramValue.substr(paramValue.length - 4) === '_asc' ? 'asc' : 'desc';
            var lengthSortDir = sortDir.length;
            paramValue = paramValue.slice(0,paramValue.length - lengthSortDir - 1);
            var decode = window.decodeURIComponent,
                urlPaths = window.location.href.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters, i, form, params, key, input, formKey;

            for (i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined ?
                    decode(parameters[1].replace(/\+/g, '%20')) :
                    '';
            }
            paramData["product_list_order"] = paramValue;
            var self = this;
            let url = new URL(window.location.href);
            if (sortDir !== undefined) {
                paramData.product_list_dir = sortDir;
            }
            paramData = $.param(paramData);
            return baseUrl + (paramData.length ? '?' + paramData : '');
        },
        getUrlParameter: function getUrlParameter(sParam) {
            var sPageURL = window.location.search.substring(1),
                sURLVariables = sPageURL.split('&'),
                sParameterName,
                i;

            for (i = 0; i < sURLVariables.length; i++) {
                sParameterName = sURLVariables[i].split('=');

                if (sParameterName[0] === sParam) {
                    return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
                }
            }
            return false;
        }
    })
});
