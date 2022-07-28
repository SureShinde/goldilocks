define([
    'mage/template',
    'Magento_Catalog/js/price-utils',
    'jquery',
    'mage/utils/wrapper'
    ], function (
    mageTemplate,utils,$, wrapper
    ) {
        'use strict';
    return function (targetModule) {

        //code here is executed
        var reloadPriceWrapper = wrapper.wrap(targetModule.prototype.reloadPrice, function () {

            var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {},
                priceTemplate = mageTemplate(this.options.priceTemplate);

            _.each(this.cache.displayPrices, function (price, priceCode) {
                price.final = _.reduce(price.adjustments, function (memo, amount) {
                    return memo + amount;
                }, price.amount);

                price.formatted = utils.formatPrice(price.final, priceFormat);
                if(priceCode === 'oldPrice' && this.cache.displayPrices.finalPrice.amount !== this.cache.displayPrices.oldPrice.amount){
                    this.element.find(`[class='old-price']`).show();
                }

                $('[data-price-type="' + priceCode + '"]', this.element).html(priceTemplate({
                    data: price
                }));
            }, this);
        });

        targetModule.prototype.reloadPrice = reloadPriceWrapper;
        return targetModule;
    };


    }
);
