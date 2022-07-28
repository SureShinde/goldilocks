define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/record',
    './time-utils'
], function (_, Element, timeUtils) {
    'use strict';

    return Element.extend({
        defaults: {
            uniqueMessage: '',
            fromBiggerThenToMessage: '',
            error: '',
            listens: {
                '${ $.provider }:data.validate': 'validate'
            }
        },

        initObservable: function () {
            this._super()
                .observe('error');

            return this;
        },

        initElement: function (field) {
            if (field.index === 'from' || field.index === 'to') {
                field.on('error', this.updateError.bind(this));
            }

            return this._super();
        },

        updateError: function () {
            this.validate();
        },

        /**
         * @param {Object} recordA
         * @param {Object} recordB
         * @return {Boolean}
         */
        isRecordsIntersect: function (recordA, recordB) {
            if (!recordA.from || !recordA.to || !recordB.from || !recordB.to) {
                return false;
            }

            return (recordA.from >= recordB.from && recordA.to <= recordB.to)
                || (recordA.from < recordB.to && recordA.from > recordB.from)
                || (recordA.to > recordB.from && recordA.to < recordB.to)
                || (recordA.from > recordB.to && recordA.from < recordB.from)
                || (recordA.to < recordB.from && recordA.to > recordB.to)
                || (recordA.from <= recordB.from && recordA.to > recordB.to);
        },

        /**
         * Validates itself by it's validation rules using validator object.
         * If validation of a rule did not pass, writes it's message to
         * 'error' observable property.
         *
         * @returns {Object} Validate information.
         */
        validate: function () {
            var sameIntervalRecord,
                parent = this.parentComponent(),
                fromField = this.getChild('from'),
                toField = this.getChild('to'),
                data = this.data(),
                idDeleted = data[parent.deleteProperty] === parent.deleteValue,
                isValid = true,
                message = '';

            if (idDeleted || !this.visible() || !this.elems().length) {
                return {
                    valid: isValid,
                    target: this
                };
            }

            data.from = timeUtils.normalizeTime(data.from);
            data.to = timeUtils.normalizeTime(data.to);

            sameIntervalRecord = _.find(parent.recordData(), function (dataB) {
                dataB.from = timeUtils.normalizeTime(dataB.from);
                dataB.to = timeUtils.normalizeTime(dataB.to);

                return data.record_id !== dataB.record_id
                    && dataB[parent.deleteProperty] !== parent.deleteValue
                    && this.isRecordsIntersect(data, dataB);
            }, this);

            if (isValid && data.from && data.to && (data.from > data.to || data.from === data.to)) {
                isValid = false;
                message = this.fromBiggerThenToMessage;
                this.source.set('params.invalid', true);
            }

            if (isValid && !_.isUndefined(sameIntervalRecord)) {
                isValid = false;
                message = this.uniqueMessage;
                this.source.set('params.invalid', true);
            }

            if (!fromField.error()
                || fromField.error() === this.fromBiggerThenToMessage
                || fromField.error() === this.uniqueMessage
            ) {
                fromField.error(message);
            }

            if (!toField.error()
                || toField.error() === this.fromBiggerThenToMessage
                || toField.error() === this.uniqueMessage
            ) {
                toField.error(message);
            }

            if (this.source && !isValid) {
                this.source.set('params.invalid', true);
            }

            return {
                valid: isValid,
                target: this
            };
        }
    });
});
