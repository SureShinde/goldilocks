define([
    'uiComponent',
    'uiRegistry'
], function (Component, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_DeliveryDateManager/deliverydate-sidebar'
        },
        _dateComponent: null,
        _timeComponent: null,
        _commentComponent: null,

        getDateComponent: function () {
            if (this._dateComponent === null) {
                this._dateComponent = registry.get({ index: 'deliverydate_date' });
            }

            return this._dateComponent;
        },

        getTimeComponent: function () {
            if (this._timeComponent === null) {
                this._timeComponent = registry.get({ index: 'deliverydate_time' });
            }

            return this._timeComponent;
        },

        getCommentComponent: function () {
            if (this._commentComponent === null) {
                this._commentComponent = registry.get({ index: 'deliverydate_comment' });
            }

            return this._commentComponent;
        },

        getDeliveryDate: function () {
            if (this.getDateComponent() && this.getDateComponent().visible()) {
                return this.getDateComponent().getPreview();
            }

            return '';
        },

        getDeliveryTime: function () {
            if (this.getTimeComponent() && this.getTimeComponent().visible()) {
                return this.getTimeComponent().getPreview();
            }

            return '';
        },

        getDeliveryComment: function () {
            if (this.getCommentComponent() && this.getCommentComponent().visible()) {
                return this.getCommentComponent().getPreview();
            }

            return '';
        }
    });
});
