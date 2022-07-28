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
    'ko',
    'jquery',
    'uiComponent',
    'prCarousel'
], function(ko, $, Component) {
    'use strict';

    var position, ajaxUrl;

    return Component.extend({
        listContent: ko.observableArray([]),
        isVisibleSliderWrapper: ko.observable(false),
        isVisibleSliderButtonWrapper: ko.observable(false),
        initialize: function (config, node) {
            position = config.position;
            ajaxUrl = config.ajaxUrl;
            this._super();

            this.loadBannerContent();
            return this;
        },

        getSliderId: function() {
            return 'pr_banner_slider_' + position;
        },

        loadBannerContent: function () {
            let self = this;
            $.ajax({
                url: ajaxUrl,
                data: {
                    'position': position,
                    'previewDate': this.previewDate,
                    'psPreviewMode': this.psPreviewMode
                },
                dataType: "json",
                success: function(result) {
                    if (result.data.length > 0) {
                        self.listContent(result.data);
                        self.isVisibleSliderWrapper(true);
                        if (result.data.length > 1) {
                            self.isVisibleSliderButtonWrapper(true);
                        }
                        $('#' + self.getSliderId()).prCarouselInit();
                    }
                }
            });
        }
    });
});
