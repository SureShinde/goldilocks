<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\CatalogInventory\Quote\Item\QuantityValidator\Initializer;

/**
 * Quote item quantity validator Stock item initializer plugin
 */
class StockItem extends \Ecombricks\Common\Plugin\Plugin
{

    /**
     * Quote item qty list
     *
     * @var \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList
     */
    protected $quoteItemQtyList;

    /**
     * Stock state
     *
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockState;

    /**
     * Type configuration
     *
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $typeConfig;

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Object\WrapperFactory $wrapperFactory
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList $quoteItemQtyList
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $typeConfig
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList $quoteItemQtyList,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $typeConfig
    )
    {
        parent::__construct($wrapperFactory);
        $this->quoteItemQtyList = $quoteItemQtyList;
        $this->stockState = $stockState;
        $this->typeConfig = $typeConfig;
    }

    /**
     * Around initialize
     *
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem $subject
     * @param \Closure $proceed
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param int $qty
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundInitialize(
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem $subject,
        \Closure $proceed,
        \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        $qty
    )
    {
        $this->setSubject($subject);
        $quoteId = $quoteItem->getQuoteId();
        $quoteItemId = $quoteItem->getId();
        $product = $quoteItem->getProduct();
        $productId = $product->getId();
        $storeId = $product->getStore()->getId();
        $quoteParentItem = $quoteItem->getParentItem();
        if ($quoteParentItem) {
            $rowQty = $quoteParentItem->getQty() * $qty;
            $qtyForCheck = $this->quoteItemQtyList->getQty($productId, $quoteItemId, $quoteId, 0);
        } else {
            $increaseQty = $quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty;
            $rowQty = $qty;
            $qtyForCheck = $this->quoteItemQtyList->getQty($productId, $quoteItemId, $quoteId, $increaseQty);
        }
        $productTypeCustomOption = $product->getCustomOption('product_type');
        if ($productTypeCustomOption !== null) {
            if ($this->typeConfig->isProductSet($productTypeCustomOption->getValue())) {
                $stockItem->setIsChildItem(true);
            }
        }
        $stockItem->setProductName($product->getName());
        $result = $this->stockState->checkQuoteItemQty($productId, $rowQty, $qtyForCheck, $qty, $storeId);
        if ($stockItem->hasIsChildItem()) {
            $stockItem->unsIsChildItem();
        }
        if ($result->getItemIsQtyDecimal() !== null) {
            $quoteItem->setIsQtyDecimal($result->getItemIsQtyDecimal());
            if ($quoteParentItem) {
                $quoteParentItem->setIsQtyDecimal($result->getItemIsQtyDecimal());
            }
        }
        if (
            $result->getHasQtyOptionUpdate() &&
            (
                !$quoteParentItem ||
                $quoteParentItem->getProduct()->getTypeInstance()->getForceChildItemQtyChanges($quoteParentItem->getProduct())
            )
        ) {
            $quoteItem->setData('qty', $result->getOrigQty());
        }
        if ($result->getItemUseOldQty() !== null) {
            $quoteItem->setUseOldQty($result->getItemUseOldQty());
        }
        if ($result->getMessage() !== null) {
            $quoteItem->setMessage($result->getMessage());
        }
        if ($result->getItemBackorders() !== null) {
            $quoteItem->setBackorders($result->getItemBackorders());
        }
        $quoteItem->setStockStateResult($result);
        return $result;
    }

}
