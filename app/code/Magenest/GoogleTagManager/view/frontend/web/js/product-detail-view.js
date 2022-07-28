/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 **/
/* global define, dataLayer */
define([
    'domReady!'
], function () {
    'use strict';

    return function (gtmConfig) {
        if (typeof dataLayer === 'undefined') {
            return;
        }

        // Push product detail into data layer
        if ('gtmProductDetail' in gtmConfig && gtmConfig.gtmProductDetail) {
            dataLayer.push({
                event: 'productDetail',
                ecommerce: {
                    'detail': gtmConfig.gtmProductDetail
                }
            });
        }

        // Push product impressions into data layer
        if ('gtmImpressions' in gtmConfig && gtmConfig.gtmImpressions) {
            dataLayer.push({
                event: 'productImpression',
                ecommerce: {
                    'impressions': gtmConfig.gtmImpressions
                }
            });
        }
    };
});
