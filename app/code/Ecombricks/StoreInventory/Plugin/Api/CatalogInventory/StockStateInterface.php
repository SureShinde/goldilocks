<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Api\CatalogInventory;

/**
 * Stock state interface plugin
 */
class StockStateInterface
{

    /**
     * Object factory
     *
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory;

    /**
     * Format
     *
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $format;

    /**
     * Get product salable qty
     *
     * @var \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface
     */
    protected $getProductSalableQty;

    /**
     * Is product salable for requested qty
     *
     * @var \Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface
     */
    protected $isProductSalableForRequestedQty;

    /**
     * Get SKUs by product IDs
     *
     * @var \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface
     */
    protected $getSkusByProductIds;

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Back order notify customer condition
     *
     * @var \Magento\InventorySales\Model\IsProductSalableCondition\BackOrderNotifyCustomerCondition
     */
    protected $backOrderNotifyCustomerCondition;

    /**
     * Get stock item configuration
     *
     * @var \Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface
     */
    protected $getStockItemConfiguration;

    /**
     * Constructor
     *
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     * @param \Magento\Framework\Locale\FormatInterface $format
     * @param \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $getProductSalableQty
     * @param \Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty
     * @param \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface $getSkusByProductIds
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @param \Magento\InventorySales\Model\IsProductSalableCondition\BackOrderNotifyCustomerCondition $backOrderNotifyCustomerCondition
     * @param \Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface $getStockItemConfiguration
     * @return void
     */
    public function __construct(
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Framework\Locale\FormatInterface $format,
        \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $getProductSalableQty,
        \Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty,
        \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface $getSkusByProductIds,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore,
        \Magento\InventorySales\Model\IsProductSalableCondition\BackOrderNotifyCustomerCondition $backOrderNotifyCustomerCondition,
        \Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface $getStockItemConfiguration
    )
    {
        $this->objectFactory = $objectFactory;
        $this->format = $format;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->isProductSalableForRequestedQty = $isProductSalableForRequestedQty;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->getStockIdByStore = $getStockIdByStore;
        $this->backOrderNotifyCustomerCondition = $backOrderNotifyCustomerCondition;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
    }

    /**
     * Get number
     *
     * @param string|float|int|null $qty
     * @return float|null
     */
    protected function getNumber($qty)
    {
        if (!is_numeric($qty)) {
            return $this->format->getNumber($qty);
        }
        return $qty;
    }

    /**
     * Around check quote item qty
     *
     * @param \Magento\CatalogInventory\Api\StockStateInterface $subject
     * @param \Closure $proceed
     * @param int $productId
     * @param float $itemQty
     * @param float $qtyToCheck
     * @param float $origQty
     * @param int|null $storeId
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundCheckQuoteItemQty(
        \Magento\CatalogInventory\Api\StockStateInterface $subject,
        \Closure $proceed,
        $productId,
        $itemQty,
        $qtyToCheck,
        $origQty,
        $storeId = null
    )
    {
        $result = $this->objectFactory->create();
        $result->setHasError(false);
        $qty = max($this->getNumber($itemQty), $this->getNumber($qtyToCheck));
        $skus = $this->getSkusByProductIds->execute([$productId]);
        $sku = $skus[$productId];
        $stockId = $this->getStockIdByStore->execute($storeId);
        $isSalableResult = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $qty);
        if ($isSalableResult->isSalable() === false) {
            foreach ($isSalableResult->getErrors() as $error) {
                $result
                    ->setHasError(true)
                    ->setMessage($error->getMessage())
                    ->setQuoteMessage($error->getMessage())
                    ->setQuoteMessageIndex('qty');
            }
        } else {
            $productSalableResult = $this->backOrderNotifyCustomerCondition->execute($sku, (int) $stockId, $qty);
            if ($productSalableResult->getErrors()) {
                foreach ($productSalableResult->getErrors() as $error) {
                    $result->setMessage($error->getMessage());
                }
            }
        }
        return $result;
    }

    /**
     * Around suggest qty
     *
     * @param \Magento\CatalogInventory\Api\StockStateInterface $subject
     * @param \Closure $proceed
     * @param int $productId
     * @param float $qty
     * @param int|null $storeId
     * @return float
     */
    public function aroundSuggestQty(
        \Magento\CatalogInventory\Api\StockStateInterface $subject,
        \Closure $proceed,
        $productId,
        $qty,
        $storeId = null
    ): float
    {
        try {
            $skus = $this->getSkusByProductIds->execute([$productId]);
            $sku = $skus[$productId];
            $stockId = $this->getStockIdByStore->execute();
            $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
            $qtyIncrements = $stockItemConfiguration->getQtyIncrements();
            if ($qty <= 0 || $stockItemConfiguration->isManageStock() === false || $qtyIncrements < 2) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Wrong condition.'));
            }
            $minQty = max($stockItemConfiguration->getMinSaleQty(), $qtyIncrements);
            $divisibleMin = ceil($minQty / $qtyIncrements) * $qtyIncrements;
            $maxQty = min(
                $this->getProductSalableQty->execute($sku, $stockId),
                $stockItemConfiguration->getMaxSaleQty()
            );
            $divisibleMax = floor($maxQty / $qtyIncrements) * $qtyIncrements;
            if ($qty < $minQty || $qty > $maxQty || $divisibleMin > $divisibleMax) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Wrong condition.'));
            }
            $closestDivisibleLeft = floor($qty / $qtyIncrements) * $qtyIncrements;
            $closestDivisibleRight = $closestDivisibleLeft + $qtyIncrements;
            $acceptableLeft = min(max($divisibleMin, $closestDivisibleLeft), $divisibleMax);
            $acceptableRight = max(min($divisibleMax, $closestDivisibleRight), $divisibleMin);
            return abs($acceptableLeft - $qty) < abs($acceptableRight - $qty) ? $acceptableLeft : $acceptableRight;
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            return $qty;
        }
    }

}
