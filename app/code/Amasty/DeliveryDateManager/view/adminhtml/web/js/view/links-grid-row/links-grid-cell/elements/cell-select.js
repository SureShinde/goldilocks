/**
 * Amasty Delivery Date Delivery Configuration Abstract Select Element
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/select',
    'uiRegistry',
    'mage/translate'
], function (
    $,
    _,
    Select,
    registry,
    $t
) {
    'use strict';

    return Select.extend({
        defaults: {
            optionsValue: '${ $.indexField }',
            optionsText: 'name',
            valueEmpty: false,
            links: {
                value: '${ $.parentName }:selectValue'
            },
            parentCellName: '${ $.parentName }',
            imports: {
                externalProvider: '${ $.parentName }:externalProvider',
                initialOptions: '${ $.externalProvider }:data'
            },
            listens: {
                initialOptions: 'onOptionsLoad',
                '${ $.externalProvider }:reload': 'showLoader',
                '${ $.externalProvider }:reloaded': 'hideLoader'
            },
            modules: {
                externalSource: '${ $.externalProvider }',
            },
            caption: $t('Choose from list...')
        },

        initialize: function () {
            this._super();
            this.onChangeValue(this.value());

            return this;
        },

        initObservable: function () {
            this.setLinks(this.imports, 'imports');

            this._super()
                .observe('options valueEmpty');

            return this;
        },

        onUpdate: function () {
            this._super();

            if (this.initialValue !== this.value()) {
                this.source.set(this.dataScope, this.value());
            }
            this.onChangeValue(this.value());
        },

        /**
         * Prepare option before set
         *
         * @param {Array} options
         * @returns {void}
         */
        onOptionsLoad: function (options) {
            if (!_.isEmpty(options) && !options.items) {
                this
                    .setOptions(this.prepareOptions(options))
                    .value(this.source.get(this.dataScope));
            }
        },

        /**
         * Necessary to right key in indexedOptions array
         *
         * @param {Array} options
         * @return {Array}
         */
        prepareOptions: function (options) {
            var prepared = [];

            _.each(options, function (option) {
                prepared.push(_.extend(option, { value: option[this.optionsValue] }));
            }.bind(this));

            return prepared;
        },

        setInitialValue: function () {
            this.initialValue = this.normalizeValue(this.source.get(this.dataScope));

            this.on('value', this.onUpdate.bind(this));

            return this;
        },

        /**
         * @param {String|*} value
         * @return {String|*}
         */
        normalizeValue: function (value) {
            return _.isEmpty(value) ? '' : value;
        },

        /**
         * @param {Number} value
         * @returns {void}
         */
        onChangeValue: function (value) {
            this.valueEmpty(_.isEmpty(value));
            this.valueEmpty.valueHasMutated();
        },

        /**
         * @param {String} target
         * @returns {void}
         */
        setDependentFieldsValue: function (target) {
            var option = this.getOption(this.value()),
                value;

            if (!_.isEmpty(option)) {
                registry.get(target, function (component) {
                    value = option[component.index];

                    if (value) {
                        // Workaround to set value to "select" element without options
                        // It's necessary to set proper value on first form load
                        _.isEmpty(component.indexedOptions)
                            ? this.source.set(component.dataScope, value)
                            : component.value(value);
                    } else {
                        component.clear();
                    }
                }.bind(this));
            }
        },

        /**
         * Set external value as main field value
         *
         * @param {*} value
         * @returns {void}
         */
        setExternalValue: function (value) {
            if (this.shouldClearOptions(value)) {
                this.setOptions([]);
                this.externalSource().data = [];
            }

            this.externalSource().reload({refresh: true});
            this.source.set(this.dataScope, value);
        },

        /**
         * Show loader on cell
         * @returns {void}
         */
        showLoader: function () {
            registry.get(this.parentCellName, function (target) {
                target.loading(true);
            });
        },

        /**
         * Hide loader on cell
         * @returns {void}
         */
        hideLoader: function () {
            registry.get(this.parentCellName, function (target) {
                target.loading(false);
            });
        },

        destroy: function (skipUpdate) {
            registry.remove(this.switcherConfig.name);

            return this._super(skipUpdate);
        },

        /**
         * Check if need to clear options when value from select was deleted
         *
         * @param {*} value
         * @return {boolean}
         */
        shouldClearOptions: function (value) {
            return value === '' && _.size(this.externalSource().data) === 1;
        }
    });
});
