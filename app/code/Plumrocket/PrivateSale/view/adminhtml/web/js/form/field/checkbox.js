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
    'Magento_Ui/js/form/element/single-checkbox',
], function ($, checkbox) {
    'use strict';

    return checkbox.extend({
        initialize: function() {
            this._super();
            this.togglePermissionsSection(this.value());

            return this;
        },
        onUpdate: function(value) {
            this.togglePermissionsSection(value);

            return this._super();
        },
        togglePermissionsSection: function(value) {
            let internal = setInterval(function () {
                let prSection = $('[data-index="private_sale"]');
                if (prSection) {
                    if (value == 0) {
                        prSection.hide();
                    } else {
                        prSection.show();
                    }
                    clearInterval(internal);
                }
            }, 500);
        }
    });
});
