<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order;

use Magento\CatalogInventory\Observer\ProductQty as NativeProductQty;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Quote\Model\Quote;

class ProductQty
{
    /**
     * @var NativeProductQty
     */
    private $productQty;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var array|null
     */
    private $placedItems = null;

    /**
     * @var Quote|null
     */
    private $quote = null;

    /**
     * @var array
     */
    private $backordered = [];

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        ModuleManager $moduleManager,
        NativeProductQty $productQty,
        CheckoutSession $checkoutSession
    ) {
        $this->productQty = $productQty;
        $this->checkoutSession = $checkoutSession;
        $this->moduleManager = $moduleManager;
    }

    public function getPlacedQty(int $productId): float
    {
        if ($this->placedItems === null && $this->isInventoryProcessed()) {
            $this->placedItems = $this->productQty->getProductQty(
                $this->getQuote()->getAllItems()
            );
        }
        $placedQty = isset($this->placedItems[$productId])
            ? $this->placedItems[$productId]
            : 0;

        return $placedQty - $this->getBackorderedQty($productId);
    }

    /**
     * Check if qty already substract from database.
     * If module Magento_InventorySales enabled than
     * plugin \Magento\InventorySales\Plugin\CatalogInventory\StockManagement\ProcessRegisterProductsSalePlugin ,
     * disable inventory processed from class \Magento\CatalogInventory\Model\StockManagement
     *
     * @return bool
     */
    private function isInventoryProcessed(): bool
    {
        return $this->getQuote()->getInventoryProcessed()
            && !$this->moduleManager->isEnabled('Magento_InventorySales');
    }

    private function getQuote(): Quote
    {
        if ($this->quote === null) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }

    public function addBackorderedQty(int $productId, float $backorderedQty): void
    {
        $this->backordered[$productId] = $backorderedQty;
    }

    public function getBackorderedQty(int $productId): float
    {
        return isset($this->backordered[$productId])
            ? $this->backordered[$productId]
            : 0;
    }
}
