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
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'uiRegistry',
    'mage/url'
], function ($, Component, uiRegistry, url) {
    'use strict';

    var parentModal;

    return Component.extend({
        initialize: function () {
            parentModal = this;
            this._super();
        },

        setHomepageToCategory: function() {
            var categorySelect = uiRegistry.get('prprivatesale_eventhomepages.prprivatesale_eventhomepages.create_new_event_homepage.general.category_container.category_event');

            var ajaxUrl = uiRegistry.get(this.provider).ajax_url,
                grid = uiRegistry.get('index = prprivatesale_eventhomepages');

            $('body').loader('show');
            $.ajax({
                url: ajaxUrl,
                data: {"categoryId" : categorySelect.value},
                type: "POST",
                success: function(response){
                    var response = JSON.parse(response);
                    if (response.result) {
                        //grid.source.reload();
                        location.reload();
                        parentModal.closeModal();
                        $('body').loader('hide');
                    }
                },
                error: function (response) {
                    $('body').loader('hide');
                }
            });
        }
    });
});
