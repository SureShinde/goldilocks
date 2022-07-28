define([
    'moment',
    'underscore',
    'ko',
    'jquery',
    'mageUtils',
    'Magento_Ui/js/form/element/date',
    'Amasty_DeliveryDateManager/js/model/channel-set-provider',
    'Amasty_DeliveryDateManager/js/model/channel-set-processor',
    'Amasty_DeliveryDateManager/js/model/date-validator',
    'Amasty_DeliveryDateManager/js/model/date-validator-cache',
    'Amasty_DeliveryDateManager/js/model/checkout-config',
    'Amasty_DeliveryDateManager/js/model/first-available-date',
    'Amasty_DeliveryDateManager/js/model/date-utils',
    'Amasty_DeliveryDateManager/js/model/datepicker'
], function (
    moment,
    _,
    ko,
    $,
    utils,
    AbstractField,
    channelSetProvider,
    channelSetProcessor,
    dateValidator,
    dateValidatorCache,
    checkoutConfig,
    firstAvailableDateModel,
    dateUtils
) {
    'use strict';

    return AbstractField.extend({
        defaults: {
            elementTmpl: 'Amasty_DeliveryDateManager/form/element/date',
            invalidDateMsg: 'Selected date is not available for delivery. Please select another one.',
            options: {
                firstDay: 1,
                buttonText: '',
                gotoCurrent: true
            },
            validation: {
                'required-entry': false
            },
            listens: {
                '${ $.provider }:${ $.dataScope }': 'setCorrectValueToProvider'
            },
            isRequired: false,
            isTimeRequired: false,
            firstAvailableDate: null
        },
        map: {
            'D': 'DDD',
            'd': 'D',
            'EEEE': 'dddd',
            'EEE': 'ddd',
            'e': 'd',
            'y': 'Y',
            'a': 'A'
        },
        dateValidationIteration: 0,
        dateValidationIterationLimit: 90,
        isDatePreselected: false,

        /**
         * Used for backend
         * @type {String}
         */
        backendInputName: 'amdeliverydate_backend_date',

        initialize: function () {
            this._super();

            this.preselectFirstAvailableDate();

            return this;
        },

        initObservable: function () {
            this._super().observe('isDatePreselected');

            return this;
        },

        initConfig: function () {
            this._super();

            dateValidatorCache.resetChannelSetData();

            this.options.beforeShow = function () {
                dateValidatorCache.resetCachedData.call(dateValidatorCache);
            };

            this.options.onChangeMonthYear = function () {
                dateValidatorCache.resetModifiersCache.call(dateValidatorCache);
                dateValidator.resetOptions();
            };

            this.options.beforeShowDay = this.restrictDatepickerDates.bind(this);

            return this;
        },

        initStatefull: function () {
            this._super();

            this.required = this.isRequired || this.isTimeRequired;
            this.validation['required-entry'] = this.isRequired || this.isTimeRequired;

            if (!this.isRequired && !this.isTimeRequired) {
                this.validationParams = null;
            }

            this.deliverydateCheckoutConfig = checkoutConfig.getDeliverydateConfig();
            this.options.firstDay = this.deliverydateCheckoutConfig.firstDay;

            return this;
        },

        /**
         * Additional validation for currently selected date.
         * @returns {Object}
         */
        validate: function () {
            var result = this._super();

            if (result.valid && !this.disabled() && this.visible() && !this.isCurrentDateValid()) {
                result.valid = false;
                this.error(this.invalidDateMsg);
                this.error.valueHasMutated();
                this.bubble('error', this.invalidDateMsg);

                if (this.source) {
                    this.source.set('params.invalid', true);
                }
            }

            return result;
        },

        /**
         * @returns {Boolean}
         */
        isCurrentDateValid: function () {
            var date;

            dateValidator.setChannelSetData();

            if (this.value()) {
                date = moment(this.value(), this.outputDateFormat).toDate();

                return this.restrictDatepickerDates(date)[0];
            }

            return true;
        },

        getElem: function () {
            return this;
        },

        prepareDateTimeFormats: function () {
            this.pickerDateTimeFormat = this.options.dateFormat;

            if (this.options.showsTime) {
                this.pickerDateTimeFormat += ' ' + this.options.timeFormat;
            }

            this.pickerDateTimeFormat = this.normalizeDate(this.pickerDateTimeFormat);

            if (this.dateFormat) {
                this.inputDateFormat = this.dateFormat;
            }

            this.inputDateFormat = this.normalizeDate(this.inputDateFormat);
            this.outputDateFormat = this.normalizeDate(this.outputDateFormat);

            this.validationParams.dateFormat = this.outputDateFormat;
        },

        /**
         * Converts mage date format to a moment.js format.
         *
         * @param {String} mageFormat
         * @returns {String}
         */
        normalizeDate: function (mageFormat) {
            var result = mageFormat;

            _.each(this.map, function (momentFormat, mage) {
                result = result.replace(new RegExp(mage, 'g'), momentFormat);
            });

            return result;
        },

        /**
         * @param {Date} date
         * @returns {Array}
         */
        restrictDatepickerDates: function (date) {
            if (dateValidator.isDateRestricted(date)) {
                return [false, ''];
            }

            return [true, ''];
        },

        /**
         * @param {Boolean} isForce
         * @param {Boolean} setToDatepicker
         * @returns {void}
         */
        preselectFirstAvailableDate: function (isForce, setToDatepicker) {
            if (!this.deliverydateCheckoutConfig.isPreselectDay) {
                return;
            }

            if (this.isDatePreselected() && !isForce) {
                return;
            }

            this.isDatePreselected(true);

            // eslint-disable-next-line vars-on-top
            var channelSet = channelSetProvider.getChannelSet(),
                firstAvailableDate = firstAvailableDateModel
                    .getChannelSetFirstAvailableDate(channelSet);

            this.onValueChange(firstAvailableDate);

            if (setToDatepicker) {
                this.setDateToDatepicker(firstAvailableDate);
            }
        },

        /**
         * Set minDate to jQuery datepicker element
         * @param {Date} date
         * @returns {void}
         */
        setMinDateToDatepicker: function (date) {
            var $datepicker = $('#' + this.uid),
                datepickerDate;

            $datepicker
                .datepicker('option', 'minDate', date);

            datepickerDate = $datepicker.datepicker('getDate');

            if (datepickerDate && datepickerDate.getDate && dateUtils.compareYMD(datepickerDate, date) === 0) {
                this.onValueChange(datepickerDate);
            }
        },

        /**
         * @param {Date|String} date
         * @returns {void}
         */
        setDateToDatepicker: function (date) {
            $('#' + this.uid).datepicker('setDate', date);
        },

        getDateFromDatepicker: function () {
            return $('#' + this.uid).datepicker('getDate');
        },

        /**
         * Validate disabled date
         *
         * @param {Date} date
         * @param {boolean} increment
         * @returns {Date|boolean}
         */
        getValidDate: function (date, increment) {
            var validationResult = this.restrictDatepickerDates(date)[0];

            if (validationResult === false) {
                if (this.dateValidationIteration > this.dateValidationIterationLimit) {
                    this.dateValidationIteration = 0;

                    return false;
                }

                if (increment) {
                    date.setDate(date.getDate() + 1);
                } else {
                    date.setDate(date.getDate() - 1);
                }

                this.dateValidationIteration++;

                return this.getValidDate(date, increment);
            }

            this.dateValidationIteration = 0;

            return date;
        },

        /**
         * Prepares and sets date/time value that will be displayed
         * in the input field.
         *
         * @param {String} value
         * @returns {void}
         */
        onValueChange: function (value) {
            var dateFormat,
                shiftedValue,
                date,
                newValue,
                validDate;

            if (value) {
                if (this.options.showsTime) {
                    shiftedValue = moment.tz(value, 'UTC').tz(this.storeTimeZone);
                } else {
                    dateFormat = this.shiftedValue() ? this.outputDateFormat : this.inputDateFormat;

                    shiftedValue = moment(value, dateFormat);
                }

                date = new Date(shiftedValue.year(), shiftedValue.month(), shiftedValue.date());
                validDate = this.getValidDate(date, true);

                if (validDate) {
                    newValue = moment(validDate).format(this.outputDateFormat);

                    if (newValue !== value) {
                        // auto correction date
                        this.value(newValue);

                        return;
                    }
                } else {
                    this.clear();

                    return;
                }

                shiftedValue = shiftedValue.format(this.pickerDateTimeFormat);
            } else {
                shiftedValue = '';
            }

            if (shiftedValue !== this.shiftedValue()) {
                this.shiftedValue(shiftedValue);
            }
        },

        getPreview: function () {
            return this.shiftedValue();
        },

        clear: function () {
            // clear input value
            $('#' + this.uid).val('');
            this.error('');
            this.value('');
            this.shiftedValue('');

            return this;
        },

        /**
         * Reset component value to provider if provider value !== component value
         * @param {String} valueFormProvider
         * @returns {void}
         */
        setCorrectValueToProvider: function (valueFormProvider) {
            if (valueFormProvider !== this.value()) {
                this.value.valueHasMutated();
            }
        }
    });
});
