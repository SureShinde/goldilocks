define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';
    $.validator.addMethod(
        'validate-email-telephone',
        function (v) {
            v = v.trim();
            $('#email').val(v);
            return $.mage.isEmptyNoTrim(v)
                || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(v)
                || (v.length > 9 && /^09\d{9}$/i.test(v));
        },
        $.mage.__('Please enter a valid email address or telephone')
    );
    $.validator.addMethod(
        'phone-validate',
        function (value) {
            value = value.trim();
            $('#telephone').val(value);
            return value === "" || value.length > 9 && /^09\d{9}$/i.test(value);
        },
        $.mage.__('Please specify a valid phone number')
    );
});
