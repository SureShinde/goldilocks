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

define([], function () {
    'use strict';
    var methods = [];

    var modifier = {
        addMethod: function (name, callback) {
            if (typeof callback === 'function') {
                methods[name] = callback;
                return this;
            }

            throw 'Callback is not a function';
        },

        execute: function (name, args = []) {
            return methods[name](...args);
        }
    };

    modifier.addMethod('updateDiscount', function (rowData) {
        if (! rowData.sale_price) {
            return rowData;
        }

        let amount = 100 - parseFloat(rowData.sale_price * 100 / rowData.product_price);
        rowData['discount_amount_percent'] = +(Math.round(amount + "e+2")  + "e-2");

        return rowData;
    });

    modifier.addMethod('updateSalePrice', function (rowData) {
        if (! rowData.discount_amount_percent) {
            return rowData;
        }

        rowData['sale_price'] = rowData.product_price - parseFloat(rowData.product_price * (rowData.discount_amount_percent / 100))
            .toFixed(2);

        return rowData;
    });

    modifier.addMethod('roundPrice', function (rowData, precision) {
        var countDigits,
            newSalePrice,
            precision = parseFloat(precision),
            price = rowData.sale_price;

        if (price) {
            countDigits = Math.ceil(Math.log10(precision));
            newSalePrice = price / (10**countDigits);
            rowData.sale_price = (10**countDigits) * (newSalePrice - (newSalePrice - parseInt(newSalePrice))) + precision;
        }

        return rowData;
    });

    return modifier;
});
