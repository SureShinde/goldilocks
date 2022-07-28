/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 **/
/* global define, vaimoGtmImpressions, dataLayer */
define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    return function () {
        var productItem = '.product-items > .product.product-item';

        if (typeof dataLayer === 'undefined' || typeof vaimoGtmImpressions === 'undefined') {
            return;
        }

        // Add product impressions
        dataLayer.push({
            event: 'productImpression',
            ecommerce: {
                'impressions': vaimoGtmImpressions
            }
        });

        // Add the gtm data to the products for productClick
        $.each(vaimoGtmImpressions, function (key, value) {
            $(productItem).eq(key).data('gtm-data', value);
        });

        // Add click event
        $(document).on('click', productItem, function () {
            const gtmData = $(this).data('gtm-data');

            dataLayer.push({
                'event': 'productClick',
                'ecommerce': {
                    'click': {
                        'actionField': {
                            'list': gtmData.list
                        },
                        'products': [gtmData]
                    }
                }
            });
        });
    };
});
