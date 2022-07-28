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
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

define([
    'Plumrocket_PrivateSale/js/grid/columns/status',
], function (Column) {
    'use strict';

    return Column.extend({

        getLabel: function(value){
            switch (parseInt(value.status)) {
                case 0:
                    return 'No';
                case 1:
                    return 'Yes';
            }

            return '';
        },
    });
});
