define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';
    $.validator.addMethod(
        'validate-email-username',
        function (v) {
            v = v.trim();
            $('#email').val(v);
            return $.mage.isEmptyNoTrim(v)
                || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(v)
                || (v.length >= 6 && v.length <= 16 && /^(?=.*[a-zA-Z])([a-zA-Z0-9\-]+)$/i.test(v));
        },
        $.mage.__('Please enter a valid email address or username')
    );
    $.validator.addMethod(
        'validate-email-telephone',
        function (v) {
            v = v.trim();
            $('#email').val(v);
            return $.mage.isEmptyNoTrim(v)
                || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(v)
                || (v.length > 9 && /^(0|\+84|84)[0-9]{9,10}$/i.test(v));
        },
        $.mage.__('Please enter a valid email address or telephone')
    );
    $.validator.addMethod(
        'validate-email-username-telephone',
        function (v) {
            v = v.trim();
            $('#email').val(v);
            return $.mage.isEmptyNoTrim(v)
                || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(v)
                || (v.length >= 6 && v.length <= 16 && /^(?=.*[a-zA-Z])([a-zA-Z0-9\-]+)$/i.test(v))
                || (v.length > 9 && /^(0|\+84|84)[0-9]{9,10}$/i.test(v));
        },
        $.mage.__('Please enter a valid email address, username or telephone')
    );
    $.validator.addMethod(
        'phone-validate',
        function (value) {
            value = value.trim();
            $('#telephone').val(value);
            return value === "" || value.length > 9 && /^(0)[0-9]{9}$/i.test(value);
        },
        $.mage.__('Please specify a valid phone number')
    );
    $.validator.addMethod(
        'username-validate',
        function (value) {
            value = value.trim();
            $('#username').val(value);
            return value.length >= 6 && value.length <= 16 && /^(?=.*[a-zA-Z])([a-zA-Z0-9\-]+)$/i.test(value);
        },
        $.mage.__('User name should be between 6 - 16 characters and only contains letters, numbers and \'-\' character, at least 1 letter.')
    );
});
