define([
    'jquery',
    'Magento_Ui/js/lib/validation/utils',
], function ($, utils) {
    'use strict';

    return function (validator) {

        validator.addRule(
            'mobileCustom',
            function (value) {
                var regExp = /[a-zA-Z]/g;
                var format = /[`!@#$%^&*()_\-=\[\]{};':"\\|,.<>\/?~]/;
                return (value.includes('+') && value.replace(" ", "").length > 9 && !regExp.test(value) && !format.test(value)) || value === '{$storeTelephone}';
            },
            $.mage.__('Please specify a valid phone number.')
        );
        validator.addRule(
            'validate-url-custom',
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = (v || '').replace(/^\s+/, '').replace(/\s+$/, '');

                return (/^(http|https|ftp):\/\/(([A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))(\.[A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))*)(:(\d+))?(\/[A-Z0-9~](([A-Z0-9_~-]|\.)*[A-Z0-9~]|))*\/?(.*)?$/i).test(v) || v === "{$baseUrl}"; //eslint-disable-line max-len

            },
            $.mage.__('Please enter a valid URL. Protocol is required (http://, https:// or ftp://).')
        )
        return validator;
    };
});
