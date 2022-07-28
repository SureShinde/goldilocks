define([
    'underscore',
    'jquery',
    'Amasty_DeliveryDateManager/js/view/fieldset',
    'Amasty_DeliveryDateManager/js/model/checkout/channel-set-provider',
    'Amasty_DeliveryDateManager/js/model/checkout/channel-set-processor',
    'Amasty_DeliveryDateManager/js/model/first-available-date',
    'Magento_Checkout/js/model/quote'
], function (
    _,
    $,
    AbstractFieldset,
    channelSetProvider,
    channelSetProcessor,
    firstAvailableDateModel,
    quote
) {
    'use strict';

    return AbstractFieldset.extend({
        defaults: {
            formSelector: '#checkout-step-shipping_method button',
            modules: {
                deliverydateDate: '${ $.name }.deliverydate_date',
                deliverydateTime: '${ $.name }.deliverydate_time',
                deliverydateComment: '${ $.name }.deliverydate_comment'
            },
            visible: false,
            firstAvailableDate: null
        },

        initialize: function () {
            this._super();

            if (quote.shippingMethod()) {
                this.shippingMethodObserver();
            }

            return this;
        },

        initObservable: function () {
            this._super();

            quote.shippingMethod.subscribe(_.debounce(this.shippingMethodObserver, 100), this);

            return this;
        },

        shippingMethodObserver: function () {
            var channelSet = channelSetProvider.getChannelSet(),
                isChannelSetExist = !_.isEmpty(channelSet.channel),
                isExistAvailableDates,
                dateComponent,
                commentComponent;

            this.isChannelSetExist(isChannelSetExist);
            this.toggleFieldsetAndChilds(isChannelSetExist);

            if (!isChannelSetExist) {
                return;
            }

            this.firstAvailableDate = firstAvailableDateModel.getChannelSetFirstAvailableDate(channelSet);
            isExistAvailableDates = !!this.firstAvailableDate;

            this.noAvailableDates(!isExistAvailableDates);

            if (!this.isAnyChildRequired()) {
                this.toggleFieldsetAndChilds(isExistAvailableDates);
            }

            dateComponent = this.deliverydateDate();
            commentComponent = this.deliverydateComment();

            if (!isExistAvailableDates) {
                if (dateComponent) {
                    dateComponent.clear();
                }

                if (commentComponent) {
                    commentComponent.clear();
                }

                return;
            }

            this.setDateComponentState();
            this.setTimeComponentState();
        },

        setDateComponentState: function () {
            var dateComponent = this.deliverydateDate();

            if (!dateComponent) {
                return;
            }

            if (!dateComponent.value() || !dateComponent.validate().valid) {
                dateComponent.preselectFirstAvailableDate(true, true);
            }
        },

        setTimeComponentState: function () {
            var timeComponent = this.deliverydateTime();

            if (!timeComponent) {
                return;
            }

            timeComponent.filter();
            timeComponent.validate();
        },

        /**
         * @param {Boolean} state
         * @returns {void}
         */
        toggleFieldsetAndChilds: function (state) {
            _.each(this.elems(), function (child) {
                child.visible(state);
            });

            this.visible(state);
        }
    });
});
