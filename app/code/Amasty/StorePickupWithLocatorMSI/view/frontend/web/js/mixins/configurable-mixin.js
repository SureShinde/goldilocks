define([
    'jquery',
    'underscore',
    'Amasty_StorePickupWithLocatorMSI/js/action/product-config',
    'Amasty_StorePickupWithLocatorMSI/js/action/toggle-locations-block',
    'Amasty_StorePickupWithLocatorMSI/js/model/msi-locations',
    'Amasty_StorePickupWithLocatorMSI/js/model/product-config'
], function ($, _, productConfigActions, toggleLocationsBlockAction, msiLocationsModel, productConfig) {
    'use strict';

    return function (configurable) {
        $.widget('mage.configurable', configurable, {

            /** @inheritdoc */
            _setupChangeEvents: function () {
                this._super();

                if (!productConfig.isConfigurable()) {
                    productConfigActions.setConfigurableState();
                    productConfigActions.setProductId(null);
                }

                productConfigActions.setMsiEnabledState();

                if (!productConfig.isMsiEnabled) {
                    return;
                }

                toggleLocationsBlockAction(msiLocationsModel.msiLocations());
            },

            /** @inheritdoc */
            _configureElement: function (element) {
                this._super(element);

                this._onOptionsChange();
            },

            _onOptionsChange: function () {
                productConfigActions.setProductId(this._getProductIdBySelectedAttributes());
                this._updateMsiLocations();
            },

            _updateMsiLocations: function () {
                var selectedProductId = productConfig.productId();

                if (!selectedProductId) {
                    msiLocationsModel.setMsiLocations([]);

                    return;
                }

                msiLocationsModel.getMsiLocationsByProductId(selectedProductId);
            },

            _getProductIdBySelectedAttributes: function () {
                var selectedAttributes = this._getSelectedOptions(),
                    productsIndicesMap = this.options.spConfig.index,
                    selectedProductId;

                $.each(productsIndicesMap, function (productId, attributes) {
                    if (_.isEqual(attributes, selectedAttributes)) {
                        selectedProductId = productId;
                    }
                });

                return selectedProductId;
            },

            _getSelectedOptions: function () {
                var selectedAttributes = {},
                    dropdownAttributes = this.options.settings;

                dropdownAttributes.each(function (index, attribute) {
                    var attributeId = attribute.attributeId,
                        optionSelected = attribute.selectedOptions[0].value;

                    if (!attributeId || !optionSelected) {
                        return;
                    }

                    selectedAttributes[attributeId] = optionSelected;
                });

                return selectedAttributes;
            }
        });

        return $.mage.configurable;
    };
});
