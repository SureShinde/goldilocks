define([
        'jquery',
        'uiComponent',
        'ko',
        'Magenest_StoreLocatorPopup/js/modal/storelocatorModal',
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
                        $.ajax({
                            url: self.urlSearchAddress,
                            type: "post",
                            data: {lat: place.geometry.location.lat(), lng: place.geometry.location.lng()},
                            showLoader: true,
                            success: function (response) {
                                self.storeList($.parseJSON(response).items)
                            }
                        });
                    });
                }, 1000);

            },
            showPopup: function (force = false) {
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: "",
                    modalClass: 'modal__nearest-store',
                    buttons: []
                };
                if (!$.cookie("nearest_store") || force) {
                    modal(options, $('#nearest-store-modal-content'));
                    $("#nearest-store-modal-content").locatorpopup("openModal");
                }
            },
            searchAddress: function () {
                var self = this;

            },
            selectStore: function (id, name) {
                var self = this;
                if($.cookie("nearest_store") && id !== $.cookie("nearest_store")) {
                    confirm({
                        title: $t('Confirm clear cart'),
                        content: $t('Cart will be empty! Do you want to change store?'),
                        actions: {
                            confirm: function () {
                                self.ajaxSelectStore(id, name);
                            },
                            cancel: function () {
                                return false;
                            }
                        }
                    });
                }else{
                    self.ajaxSelectStore(id, name);
                }
            },

            ajaxSelectStore(id,name){
                var self = this;
                $.ajax({
                    showLoader: true,
                    url: self.urlSelectStore,
                    data: { storeId: id, storeName: name},
                    type: "POST",
                    success: function (response) {
                        $.cookie("nearest_store", id);
                        $.cookie("nearest_store_name", name);
                        var redirect_url = $.parseJSON(response).redirect_url
                        location.href = redirect_url;
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
