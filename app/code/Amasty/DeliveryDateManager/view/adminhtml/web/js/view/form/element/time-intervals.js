define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    './time-utils'
], function (_, Element, timeUtils) {
    'use strict';

    return Element.extend({
        defaults: {
            requiredMessage: '',
            uniqueMessage: '',
            message: '',
            listens: {
                '${ $.provider }:data.validate': 'onValidate',
                '${ $.provider }:data.clear': 'clearRows',
                '${ $.provider }:data.overload': 'reload',
                '${ $.provider }:data.reset': 'reset'
            }
        },

        initObservable: function () {
            this._super().observe('message');

            return this;
        },

        reload: function () {
            this._super();
            this.checkSpinner();
            this.message('');
        },

        /**
         * Clear rows and re-init children
         * @returns {void}
         */
        clearRows: function () {
            this.recordData([]);
            this.reload();
            this.checkSpinner();
        },

        /**
         * Init header elements.
         * Override for fix required statement.
         *
         * @returns {void}
         */
        initHeader: function () {
            var labels = [],
                data;

            if (!this.labels().length) {
                _.each(this.childTemplate.children, function (cell) {
                    data = this.createHeaderTemplate(cell.config);
                    cell.config.labelVisible = false;
                    _.extend(data, {
                        defaultLabelVisible: data.visible(),
                        label: cell.config.label,
                        name: cell.name,
                        required: !!cell.config.validation && !!cell.config.validation['required-entry'],
                        columnsHeaderClasses: cell.config.columnsHeaderClasses,
                        sortOrder: cell.config.sortOrder
                    });
                    labels.push(data);
                }, this);
                this.labels(_.sortBy(labels, 'sortOrder'));
            }
        },

        /**
         * Validate all dynamic rows to ensure that all valid on all pages
         *
         * @returns {void}
         */
        onValidate: function () {
            var error = '';

            if (this.recordData().length === 0) {
                this.source.set('params.invalid', true);
                error = this.requiredMessage;
            }

            this.recordData.each(function (dataA) {
                var invalidRecord;

                dataA.from = timeUtils.normalizeTime(dataA.from);
                dataA.to = timeUtils.normalizeTime(dataA.to);

                if (dataA[this.deleteProperty] === this.deleteValue) {
                    return; // continue
                }

                invalidRecord = _.find(this.recordData(), function (dataB) {
                    dataB.from = timeUtils.normalizeTime(dataB.from);
                    dataB.to = timeUtils.normalizeTime(dataB.to);

                    return dataA.record_id !== dataB.record_id
                        && dataB[this.deleteProperty] !== this.deleteValue
                        && this.isRecordsIntersect(dataA, dataB);
                }, this);

                if (!_.isUndefined(invalidRecord)) {
                    this.source.set('params.invalid', true);
                    error = this.uniqueMessage;
                }
            }, this);

            this.message(error);
        },

        /**
         * @param {Object} recordA
         * @param {Object} recordB
         * @return {boolean}
         */
        isRecordsIntersect: function (recordA, recordB) {
            return (recordA.from >= recordB.from && recordA.to <= recordB.to)
                || (recordA.from < recordB.to && recordA.from > recordB.from)
                || (recordA.to > recordB.from && recordA.to < recordB.to);
        }
    });
});
