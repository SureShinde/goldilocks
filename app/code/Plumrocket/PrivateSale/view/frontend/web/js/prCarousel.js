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

require([
    'jquery'
], function ($) {
    "use strict";

    (function ($) {
        $.fn.prCarouselInit = function () {
            var carousel = $(this),
                sliderFlag = true,
                slideCount;

            carousel.parent().next().find('.prcarousel_next').click(function () {
                shiftSlide(-1)
            });
            carousel.parent().next().find('.prcarousel_prev').click(function () {
                shiftSlide(1)
            });
            slideCount = carousel.find('.prslide').length;

            if (slideCount > 1) {
                carousel.on('mouseenter', function () {
                    sliderFlag = false;
                });
                carousel.on('mouseleave', function () {
                    sliderFlag = true;
                });
                setInterval(function () {
                    if (sliderFlag) {
                        shiftSlide(-1);
                    }
                }, 5000);
            }

            function shiftSlide(direction)
            {
                if (carousel.hasClass('transition')) {
                    return;
                }

                var slideWidth = $(carousel).width();
                $(document).off('mouseup');
                carousel.off('mousemove')
                    .addClass('transition')
                    .css('transform','translateX(' + (direction * slideWidth) + 'px)');

                setTimeout(function () {
                    if (direction === 1) {
                        carousel.find('.prslide:first').before(carousel.find('.prslide:last'));
                    } else if (direction === -1) {
                        carousel.find('.prslide:last').after(carousel.find('.prslide:first'));
                    }

                    carousel.removeClass('transition');
                    carousel.css('transform','translateX(0px)');
                }, 700);
            }

            return this;
        };
    }($));
});
