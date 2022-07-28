/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'matchMedia',
    'mage/smart-keyboard-handler',
    'mage/mage',
    'mage/ie-class-fixer',
    'slick',
    'domReady!'
], function ($, mediaCheck, keyboardHandler) {
    'use strict';

    $('.cart-summary').mage('sticky', {
        container: '#maincontent'
    });

    $('.panel.header > .header.links').clone().appendTo('#store\\.links');
    $('#store\\.links li a').each(function () {
        var id = $(this).attr('id');

        if (id !== undefined) {
            $(this).attr('id', id + '_mobile');
        }
    });

    $('body').on('click', '.opc-block-summary .table-totals .grand .amount', function () {
        $(this).parents('.table-totals').toggleClass('collapsed');
    });

    // Home Promos Block
    if ($('.home-promos-block.slider-desktop .pagebuilder-column-group').length) {
        mediaCheck({
            media: '(max-width: 768px)',
            entry: $.proxy(function () {
                if ($('.home-promos-block.slider-desktop .pagebuilder-column-group.slick-initialized').length) {
                    $('.home-promos-block.slider-desktop .pagebuilder-column-group.slick-initialized').slick('unslick');
                }
            }, this),
            exit: $.proxy(function () {
                $('.home-promos-block.slider-desktop .pagebuilder-column-group').slick({
                    arrows: true,
                    dots: true,
                    slidesToShow: 4,
                    slidesToScroll: 4
                });
            }, this)
        });
    }

    keyboardHandler.apply();
});
