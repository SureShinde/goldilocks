define([
        'jquery',
        'uiComponent',
        'ko',
        'Magento_Ui/js/modal/modal',
        'mage/translate',
        'Magento_Ui/js/modal/confirm'
    ], function ($, Component, ko, modal, $t, confirm) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magenest_StoreLocatorPopup/nearest-store',
                storeList: ko.observableArray([]),
                address: ko.observable(''),
                urlSearchAddress: ko.observable(''),
                urlSelectStore: ko.observable(''),
                baseUrlStore: ko.observable(''),
                lat: ko.observable(''),
                lng: ko.observable(''),
            },
            initialize: function () {
                this._super();
                var self = this;
                setTimeout(function () {
                    let autocomplete;
                    autocomplete = new google.maps.places.Autocomplete(
                        (document.getElementsByName("address")[0]),
                        {types: ['geocode']}
                    );
                    google.maps.event.addListener(autocomplete, 'place_changed', function () {
                        var place = autocomplete.getPlace();
                        self.lat(place.geometry.location.lat());
                        self.lng(place.geometry.location.lng());
                    });
                }, 1000);

            },
            showPopup: function (force = false) {
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: $t('Choose the nearest store'),
                    buttons: [{
                        text: $t('Close'),
                        class: 'modal-close',
                        click: function () {
                            this.closeModal();
                        }
                    }]
                };
                if (!$.cookie("nearest_store") || force) {
                    modal(options, $('#nearest-store-modal-content'));
                    $("#nearest-store-modal-content").modal("openModal");
                }
            },
            searchAddress: function () {
                var self = this;
                $.ajax({
                    url: self.urlSearchAddress,
                    type: "post",
                    data: {lat: self.lat, lng: self.lng},
                    showLoader: true,
                    success: function (response) {
                        self.storeList($.parseJSON(response).items)
                    }
                });
            },
            selectStore: function (id, name) {
                var self = this;
                confirm({
                    title: $t('Confirm clear cart'),
                    content: $t('Cart will be empty! Do you want to change store?'),
                    actions: {
                        confirm: function () {
                            $.ajax({
                                showLoader: true,
                                url: self.urlSelectStore,
                                data: { storeId: id, storeName: name},
                                type: "POST",
                                success: function () {
                                    $.cookie("nearest_store", id);
                                    $.cookie("nearest_store_name", name);
                                    window.location.reload();
                                }
                            });
                        },
                        cancel: function () {
                            return false;
                        }
                    }
                });
            },

            formatDistance: function (distance) {
                return Number(distance).toFixed(2) + ' km';
            },
            viewDetail: function (urlKey) {
                var self = this;
                var url = self.baseUrlStore + urlKey
                window.open(url, '_blank')
            }
        });
    }
);
