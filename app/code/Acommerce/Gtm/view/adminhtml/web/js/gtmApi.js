define([
	'jquery',
	'Magento_Ui/js/modal/alert'
], function ($, alert) {
	"use strict";

	var GTMAPI = GTMAPI || {};

	var triggerButton = $('#save_gtm_api'),
		accountID = $('#Acommerce_Gtm_api_account_id'),
		containerID = $('#Acommerce_Gtm_api_container_id'),
		uaTrackingID = $('#Acommerce_Gtm_api_ua_tracking_id'),
		ipAnonymization = $('#Acommerce_Gtm_api_ip_anonymization'),
		formKey = $('#api_form_key');

	var conversionTrackingButton = $('#save_gtm_api_conversion_tracking'),
		conversionId = $('#Acommerce_Gtm_adwords_conversion_tracking_google_conversion_id'),
		conversionLabel = $('#Acommerce_Gtm_adwords_conversion_tracking_google_conversion_label'),
		conversionCurrencyCode = $('#Acommerce_Gtm_adwords_conversion_tracking_google_conversion_currency_code');


	GTMAPI.initialize = function (itemPostUrl) {
		var that = this;
		$(triggerButton).click(function() {
			var validation = that._validateInputs();
			if (validation.length) {
				alert({content: validation.join('')});
			} else {
				$.ajax({
					showLoader: true,
					url: itemPostUrl,
					data: {
						'form_key' : formKey.val(),
						'account_id' : accountID.val().trim(),
						'container_id' : containerID.val().trim(),
						'ua_tracking_id' : uaTrackingID.val().trim(),
						'ip_anonymization' : ipAnonymization.val()
					},
					type: "POST",
					dataType: 'json'
				}).done(function (data) {
					alert({content: data.join('<br/>')});
				});
			}
		});
	};

	GTMAPI.initializeConversionTracking = function (itemPostUrl) {
		var that = this;
		$(conversionTrackingButton).click(function() {
			var validation = that._validateConversionTrackingInputs();
			if (validation.length) {
				alert({content: validation.join('')});
			} else {
				$.ajax({
					showLoader: true,
					url: itemPostUrl,
					data: {
						'form_key' : formKey.val(),
						'account_id' : accountID.val().trim(),
						'container_id' : containerID.val().trim(),
						'conversion_id' : conversionId.val().trim(),
						'conversion_label' : conversionLabel.val().trim(),
						'conversion_currency_code' : conversionCurrencyCode.val().trim()
					},
					type: "POST",
					dataType: 'json'
				}).done(function (data) {
					alert({content: data.join('<br/>')});
				});
			}
		});
	};

	GTMAPI._validateInputs = function () {
		var errors = [];
		if (accountID.val().trim() == '') {
			errors.push('Please specify the Account ID <br/>');
		}
		if (containerID.val().trim() == '') {
			errors.push('Please specify the Container ID <br/>');
		}
		if (uaTrackingID.val().trim() == '') {
			errors.push('Please specify the Universal Tracking ID <br/>');
		}

		return errors;
	};


	GTMAPI._validateConversionTrackingInputs = function () {
		var errors = [];
		if (accountID.val().trim() == '') {
			errors.push('Please specify the Account ID in GTM API Configuration section <br/>');
		}
		if (containerID.val().trim() == '') {
			errors.push('Please specify the Container ID in GTM API Configuration section <br/>');
		}
		if (conversionId.val().trim() == '') {
			errors.push('Please specify the Google Conversion Id <br/>');
		}
		if (conversionLabel.val().trim() == '') {
			errors.push('Please specify the Google Conversion Label <br/>');
		}
		if (conversionCurrencyCode.val().trim() == '') {
			errors.push('Please specify the Google Convesion Currnecy Code <br/>');
		}

		return errors;
	};

	return GTMAPI;
});