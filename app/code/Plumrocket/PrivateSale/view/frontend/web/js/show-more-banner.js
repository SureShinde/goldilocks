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
    "jquery"
], function($) {
    "use strict";
    let bannerDestiption = $('.header-three .prprivatesale-header-desc div');
    let showMoreButton = $('.header-three .prprivatesale-header-desc button');

    if ($(window).width() < 640 
    && $(".prprivatesale-category-header").hasClass("header-three")) {
      bannerDestiption.css("display", "inline");
      let splitBannerText = bannerDestiption.html().split(' ');
      let cutBannerText = splitBannerText.slice(0, 11);

      if (splitBannerText.length > 15) {
        bannerDestiption.html(cutBannerText.join(' ') + '...');

        showMoreButton.click(function(){
          cutBannerText = splitBannerText.slice();
          bannerDestiption.html(cutBannerText.join(' '));
          showMoreButton.remove();
        });
      } else {
          showMoreButton.remove();
      }
    }
});
