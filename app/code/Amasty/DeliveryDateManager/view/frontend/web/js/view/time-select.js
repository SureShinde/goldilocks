define([
    'underscore',
    'Magento_Ui/js/form/element/select',
    'Amasty_DeliveryDateManager/js/model/channel-set-processor',
    'Amasty_DeliveryDateManager/js/model/checkout-config'
], function (
    _, AbstractField, channelSetProcessor, checkoutConfig
) {
    'use strict';

    return AbstractField.extend({
        defaults: {
            disabled: true,
            elementTmpl: 'Amasty_DeliveryDateManager/form/element/select',
            isRequired: false,
            modules: {
                dateComponent: '${$.parentName}.deliverydate_date'
            },
            cache: {
                previousTimeIntervalsCacheKey: ''
            }
        },

        labelCache: {
            dateForLabel: new Date(),
            formatter: new Intl.DateTimeFormat([], { hour: '2-digit', minute: '2-digit' })
        },

        initStatefull: function () {
            this._super();

            this.required = this.isRequired;
            this.validation['required-entry'] = this.isRequired;
            this.deliverydateCheckoutConfig = checkoutConfig.getDeliverydateConfig();

            return this;
        },

        /**
         * Store initial value even if no such option in select
         * @returns {Element}
         */
        setInitialValue: function () {
            var initialValue = this.value() || this.default;

            this._super();

            if (!this.initialValue) {
                this.initialValue = initialValue;
            }

            return this;
        },

        /**
         * Filter time intervals array after date value update
         * @returns {void}
         */
        filter: function () {
            var timeIntervals,
                timeIntervalIds = [],
                timeIntervalsCacheKey = '',
                date;

            date = this.dateComponent()
                ? this.dateComponent().value()
                : null;

            if (!date) {
                this.value(null);
                this.disable();

                return;
            }

            this.enable();

            timeIntervals = channelSetProcessor.getTimeIntervalsByDate(new Date(date));

            _.each(timeIntervals, function (interval) {
                timeIntervalIds.push(interval.interval_id);
            });

            timeIntervalsCacheKey = timeIntervalIds.toString();

            if (this.cache.previousTimeIntervalsCacheKey !== timeIntervalsCacheKey) {
                this.cache.previousTimeIntervalsCacheKey = timeIntervalsCacheKey;
                this.updateOptionsByTimeIntervals(timeIntervals);
            }
        },

        /**
         * @param {TimeInterval[]} timeIntervals
         * @returns {void}
         */
        updateOptionsByTimeIntervals: function (timeIntervals) {
            var options = [];

            timeIntervals.forEach(function (timeInterval) {
                var label = this.createLabel(timeInterval);

                if (timeInterval.label) {
                    label += ' ' + timeInterval.label;
                }

                options.push({
                    value: timeInterval.interval_id,
                    label: label
                });
            }, this);

            this.setOptions(options);
            this.setPreselectedInterval();
        },

        /**
         * Create label for Time Interval
         * @param {TimeInterval} timeInterval
         * @returns {string}
         */
        createLabel: function (timeInterval) {
            var labelDate = this.labelCache.dateForLabel,
                label = '';

            labelDate.setHours(0);
            labelDate.setSeconds(0);
            labelDate.setMinutes(timeInterval.from);
            label = this.labelCache.formatter.format(labelDate) + ' - ';

            labelDate.setHours(0);
            labelDate.setSeconds(0);
            labelDate.setMinutes(timeInterval.to);
            label += this.labelCache.formatter.format(labelDate);

            return label;
        },

        setPreselectedInterval: function () {
            if (!this.deliverydateCheckoutConfig.isPreselectTime) {
                return;
            }

            if (this.options().length) {
                this.value(this.options()[0].value);
            }
        },

        /**
         * Set initial value if new options are set with option as initialValue
         * @returns {Element}
         */
        setOptions: function () {
            this._super();

            if (!this.value() && this.initialValue) {
                this.value(this.normalizeData(this.initialValue));
            }

            return this;
        },

        /**
         * Reset initial value when customer wants to unselect value
         * for avoid preselect in setOptions
         * @returns {void}
         */
        onUpdate: function () {
            if (_.isUndefined(this.value()) && this.options().length) {
                this.initialValue = '';
            }

            this._super();
        }
    });
});
