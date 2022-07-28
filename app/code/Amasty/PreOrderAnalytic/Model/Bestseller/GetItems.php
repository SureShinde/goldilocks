<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\Bestseller;

use Amasty\PreOrderAnalytic\Model\Price\Convert as PriceConvert;
use Amasty\PreOrderAnalytic\Model\ResourceModel\LoadBestsellerPreorders;
use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Store\Model\Store;

class GetItems
{
    const DEFAULT_LIMIT = 10;

    /**
     * @var LoadBestsellerPreorders
     */
    private $loadBestsellerPreorders;

    /**
     * @var PriceConvert
     */
    private $priceConvert;

    /**
     * @var CurrencyModel
     */
    private $currencyModel;

    public function __construct(
        LoadBestsellerPreorders $loadBestsellerPreorders,
        PriceConvert $priceConvert,
        CurrencyModel $currencyModel
    ) {
        $this->loadBestsellerPreorders = $loadBestsellerPreorders;
        $this->priceConvert = $priceConvert;
        $this->currencyModel = $currencyModel;
    }

    public function execute(): array
    {
        $baseCurrenciesCount = count($this->currencyModel->getConfigBaseCurrencies());
        // calculate max possible limit because we aggregate by product_id, currency.
        // finish limit apply in this class with array_slice
        $bestsellerRows = $this->loadBestsellerPreorders->execute($baseCurrenciesCount * self::DEFAULT_LIMIT);

        $bestsellers = [];
        foreach ($bestsellerRows as $bestsellerRow) {
            if (!isset($bestsellers[$bestsellerRow[LoadBestsellerPreorders::PRODUCT_ID_COLUMN]])) {
                $bestsellers[$bestsellerRow[LoadBestsellerPreorders::PRODUCT_ID_COLUMN]] = array_intersect_key(
                    $bestsellerRow,
                    array_flip([LoadBestsellerPreorders::NAME_COLUMN, LoadBestsellerPreorders::PRODUCT_ID_COLUMN])
                );
                $bestsellers[$bestsellerRow[LoadBestsellerPreorders::PRODUCT_ID_COLUMN]]
                    [LoadBestsellerPreorders::QTY_COLUMN] = 0.0;
                $bestsellers[$bestsellerRow[LoadBestsellerPreorders::PRODUCT_ID_COLUMN]]
                    [LoadBestsellerPreorders::REVENUE_COLUMN] = 0.0;
            }

            $qty = $bestsellerRow[LoadBestsellerPreorders::QTY_COLUMN];
            $currency = $bestsellerRow[LoadBestsellerPreorders::CURRENCY_CODE_COLUMN];
            $revenue = $this->priceConvert->execute(
                (float) $bestsellerRow[LoadBestsellerPreorders::REVENUE_COLUMN],
                $currency
            );

            $bestsellers[$bestsellerRow[LoadBestsellerPreorders::PRODUCT_ID_COLUMN]]
                [LoadBestsellerPreorders::REVENUE_COLUMN] += $revenue;
            $bestsellers[$bestsellerRow[LoadBestsellerPreorders::PRODUCT_ID_COLUMN]]
                [LoadBestsellerPreorders::QTY_COLUMN] += $qty;
        }

        $bestsellers = array_slice($bestsellers, 0, self::DEFAULT_LIMIT);

        array_multisort(
            array_column($bestsellers, LoadBestsellerPreorders::QTY_COLUMN),
            SORT_DESC,
            array_column($bestsellers, LoadBestsellerPreorders::REVENUE_COLUMN),
            SORT_DESC,
            $bestsellers
        );

        return $bestsellers;
    }
}
