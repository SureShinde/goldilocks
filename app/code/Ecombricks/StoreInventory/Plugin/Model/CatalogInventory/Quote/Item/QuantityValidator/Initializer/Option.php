<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\CatalogInventory\Quote\Item\QuantityValidator\Initializer;

/**
 * Quote item quantity validator option initializer plugin
 */
class Option extends \Ecombricks\Common\Plugin\Plugin
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
     * Constructor
     *
     * @param \Ecombricks\Common\Object\WrapperFactory $wrapperFactory
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList $quoteItemQtyList
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList $quoteItemQtyList,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState
    )
    {
        parent::__construct($wrapperFactory);
        $this->quoteItemQtyList = $quoteItemQtyList;
        $this->stockState = $stockState;
    }

    /**
     * Around initialize
     *
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item\Option $option
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param int $qty
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundInitialize(
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\Option $option,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        $qty
    )
    {
        $this->setSubject($subject);
        $product = $option->getProduct();
        $productId = $product->getId();
        $storeId = $product->getStore()->getId();
        $optionValue = $option->getValue();
        $optionQty = $qty * $optionValue;
        $increaseOptionQty = ($quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty) * $optionValue;
        $qtyForCheck = $this->quoteItemQtyList->getQty($productId, $quoteItem->getId(),  $quoteItem->getQuoteId(), $increaseOptionQty);
        $stockItem = $subject->getStockItem($option, $quoteItem);
        $stockItem->setProductName($product->getName());
        $result = $this->stockState->checkQuoteItemQty($productId, $optionQty, $qtyForCheck, $optionValue, $storeId);
        if ($result->getItemIsQtyDecimal() !== null) {
            $option->setIsQtyDecimal($result->getItemIsQtyDecimal());
        }
        if ($result->getHasQtyOptionUpdate()) {
            $option->setHasQtyOptionUpdate(true);
            $quoteItem->updateQtyOption($option, $result->getOrigQty());
            $option->setValue($result->getOrigQty());
            $quoteItem->setData('qty', (int) $qty);
        }
        if ($result->getMessage() !== null) {
            $option->setMessage($result->getMessage());
            $quoteItem->setMessage($result->getMessage());
        }
        if ($result->getItemBackorders() !== null) {
            $option->setBackorders($result->getItemBackorders());
        }
        $stockItem->unsIsChildItem();
        $option->setStockStateResult($result);
        return $result;
    }

}
