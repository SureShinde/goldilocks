<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\Price;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Convert
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
    }

    public function execute(float $price, string $fromCurrency): float
    {
        $fromCurrency = $this->priceCurrency->getCurrency(null, $fromCurrency);
        $toCurrency = $this->storeManager->getStore(Store::DEFAULT_STORE_ID)->getBaseCurrency();
        $rate = $fromCurrency->getRate($toCurrency);
        if ($rate) {
            $result = $fromCurrency->convert($price, $toCurrency);
        } else {
            $result = $price;
        }

        return $result;
    }
}
