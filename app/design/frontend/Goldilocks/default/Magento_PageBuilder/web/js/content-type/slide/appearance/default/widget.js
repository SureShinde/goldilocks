
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'Magento_PageBuilder/js/widget/show-on-hover',
    'Magento_PageBuilder/js/widget/video-background'
], function ($, _, showOnHover, videoBackground) {
    'use strict';

    return function (config, element) {
        var videoElement = element[0].querySelector('[data-background-type=video]'),
            imageElement = element[0].querySelector('[data-background-type=image]'),
            viewportElement = document.createElement('div'),
            $slider = null;

        showOnHover(config);

        if (videoElement) {
            $slider = $(element).closest('[data-content-type=slider]');
            viewportElement.classList.add('jarallax-viewport-element');
            videoElement.setAttribute('data-element-in-viewport', '.jarallax-viewport-element');
            videoElement.appendChild(viewportElement);
            videoBackground(config, videoElement);

            if ($slider.data('afterChangeIsSet')) {
                return;
            }

            $slider.on('afterChange init', function () {
                var videoSlides = $slider[0].querySelectorAll('.jarallax');

                _.each(videoSlides, function (videoSlide) {
                    videoSlide.jarallax && videoSlide.jarallax.onScroll();
                });
            });
            $slider.data('afterChangeIsSet', true);
        }

        if (element[0].classList.contains('image-slide') && imageElement) {
            var imagesValueStr = element[0].querySelector('[data-background-type=image]').getAttribute('data-background-images').replace(/\\(.)/mg, "$1");
            var imagesValue = JSON.parse(imagesValueStr);
            var imageDesktop = '',
                imageMobile = '';
            if (imagesValue['desktop_image']) {
                imageDesktop = imagesValue['desktop_image'];
                imageMobile = imagesValue['desktop_image'];
            }
            if (imagesValue['mobile_image']) {
                imageMobile = imagesValue['mobile_image'];
            }
            element[0].querySelector('.pagebuilder-overlay').insertAdjacentHTML('beforebegin', '<div class="pagebuilder-image-slide"><img class="image-slide-desktop" src="' + imageDesktop + '" alt=""/><img class="image-slide-mobile" src="' + imageMobile + '" alt=""/></div>');
        }
    };
});
