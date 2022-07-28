define([
    'underscore',
    'jquery',
    'uiComponent'
], function (_, $, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_DeliveryDateManager/deliverydate',
            visible: true,
            noAvailableDates: false,
            isChannelSetExist: true
        },

        initObservable: function () {
            this._super()
                .observe(['visible', 'noAvailableDates', 'isChannelSetExist']);

            return this;
        },

        initStatefull: function () {
            this._super();

            this.deliverydateConfig = window.checkoutConfig.amasty.deliverydate;

            return this;
        },

        validate: function () {
            var allFieldsValid = true,
                notValidField = null;

            if (this.deliverydateDate() && !this.deliverydateDate().validate().valid) {
                notValidField = this.deliverydateDate().validate().target;
                allFieldsValid = false;
            }

            if (this.deliverydateTime() && !this.deliverydateTime().validate().valid) {
                notValidField = this.deliverydateTime().validate().target;
                allFieldsValid = false;
            }

            if (this.deliverydateComment() && !this.deliverydateComment().validate().valid) {
                notValidField = this.deliverydateComment().validate().target;
                allFieldsValid = false;
            }

            if (!allFieldsValid) {
                notValidField.focused(true);
            }

            return allFieldsValid;
        },

        showDeliveryRulesBlock: function () {
            return this.deliverydateConfig.deliveryRulesBlock;
        },

        isAnyChildRequired: function () {
            return _.some(this.elems(), function (child) {
                return child.isRequired;
            });
        }
    });
});
