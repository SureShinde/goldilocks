/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales v4.x.x
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

define([
    "jquery",
    'Plumrocket_PrivateSale/js/model/countdown/layout',
    'jquery/ui',
    "Plumrocket_PrivateSale/js/countdown/jquery.countdown",
    "domReady!"
], function ($, layout) {
    "use strict";

    $.widget('plumrocket.privatesaleEvent', {

        /**
         * Events end time
         */
        eventEndTime: {},

        /**
         * Contain layout name for events by id
         *
         * @type {{}}
         */
        eventLayouts: {},

        _create: function () {
            var self = this;
            $(function () {
                self.getTimes.call(self, self.callback);
            });
            layout.int(this.options.countdownLayouts);
        },

        getTimes: function (callback) {

            var eventEndTime = this.eventEndTime,
                self = this;

            if (jQuery.isEmptyObject(eventEndTime)) {
                return false;
            }

            $.ajax({
                url: this.options.timeUrl,
                dataType: 'json',
                method: 'post',
                data: {
                    'data': eventEndTime,
                    'previewDate': this.options.previewDate,
                    'psPreviewMode': this.options.previewMode
                }
            })
                .success(function (response) {
                    if (response.success) {
                        callback.call(self, response.data);
                        /* Fix for ultimo grid */
                        if (window.setGridItemsEqualHeight) {
                            setTimeout(function () {
                                window.setGridItemsEqualHeight(jQuery); /*use native jquery*/
                            }, 500);
                        }
                        /* end */
                        return response.data;
                    } else {
                        console.error('Something went wrong. Times not loaded');
                    }
                })
                .error(function () {
                    console.error('Something went wrong. Times not loaded');
                });
        },

        callback: function (result) {
            if (result) {
                var self = this;
                $.each(result, function (eventId, timeToEnd) {

                    if (timeToEnd > 0) {
                        var $item = $('.privatesale-countdown-timer[data-item="' + eventId + '"]');
                        if ($item.length) {
                            $item.show();
                            $item.find('.timer').countdown({
                                until: +timeToEnd,
                                format: 'dhMS',
                                layout: self.getCountdownLayout(eventId, timeToEnd),
                                padZeroes: true,
                                labels: self.options.countdownLabelsFew.split(','),
                                // The display texts for the counters if only one
                                labels1: self.options.countdownLabelsOne.split(','),
                                onExpiry: function () {
                                    $.ajax({
                                        url: self.options.privateSaleCacheCleanUrl,
                                        dataType: 'json',
                                        method: 'post',
                                        data: {}
                                    })
                                        .success(function (response) {
                                            if (response.success) {
                                                setTimeout(function () {
                                                    window.location.reload();
                                                }, 1000);
                                            } else {
                                                console.error(response.message);
                                            }
                                        })
                                        .error(function () {
                                            console.error('Something went wrong.');
                                        });
                                }
                            });
                        }
                    }
                });
            }
        },

        /**
         * @param {Integer} eventId
         * @param {Integer} time
         * @param {String}  layout
         * @param {String}  page
         */
        addItem: function (eventId, time, layout, page) {
            this.eventEndTime[eventId] = time;
            this.eventLayouts[eventId] = {type: layout, page: page};
        },

        /**
         * @param {Integer} eventId
         * @param {Integer} timeToEnd
         */
        getCountdownLayout: function (eventId, timeToEnd) {
            if (this.eventLayouts[eventId]) {
                return layout.getFormat(this.eventLayouts[eventId], timeToEnd);
            }

            console.warn('Private Sale: Can\'t find countdown layout for event "' + eventId + '"');
            return '{dn} {dl}, {hnn}:{mnn}:{snn}';
        },

        /**
         * @param {Object} eventEndTime
         */
        setData: function (eventEndTime) {
            this.eventEndTime = eventEndTime;
        }
    });

    return $.plumrocket.privatesaleEvent;
});
