<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\CatalogInventory\Quote\Item;

/**
 * Quote item quantity validator plugin
 */
class QuantityValidator extends \Ecombricks\Common\Plugin\Plugin
{

    /**
     * Error origin
     */
    const ERROR_ORIGIN = 'cataloginventory';

    /**
     * Add quote item error info
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param string $code
     * @param string|null $message
     * @return $this
     */
    protected function addQuoteItemErrorInfo(\Magento\Quote\Model\Quote\Item $quoteItem, $code, $message)
    {
        $quoteItem->addErrorInfo(static::ERROR_ORIGIN, $code, $message);
        return $this;
    }

    /**
     * Add quote item qty error info
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param string|null $message
     * @return $this
     */
    protected function addQuoteItemQtyErrorInfo(\Magento\Quote\Model\Quote\Item $quoteItem, $message)
    {
        return $this->addQuoteItemErrorInfo($quoteItem, \Magento\CatalogInventory\Helper\Data::ERROR_QTY, $message);
    }

    /**
     * Add quote error info
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param string $messageIndex
     * @param string $code
     * @param string|null $message
     * @return $this
     */
    protected function addQuoteErrorInfo(
        \Magento\Quote\Model\Quote\Item $quoteItem,
        $messageIndex,
        $code,
        $message
    )
    {
        $quoteItem->getQuote()->addErrorInfo($messageIndex, static::ERROR_ORIGIN, $code, $message);
        return $this;
    }

    /**
     * Add quote qty error info
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param string $messageIndex
     * @param string|null $message
     * @return $this
     */
    protected function addQuoteQtyErrorInfo(
        \Magento\Quote\Model\Quote\Item $quoteItem,
        $messageIndex,
        $message
    )
    {
        return $this->addQuoteErrorInfo($quoteItem, $messageIndex, \Magento\CatalogInventory\Helper\Data::ERROR_QTY, $message);
    }

    /**
     * Add quote item error info from result
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param \Magento\Framework\DataObject $result
     * @return $this
     */
    protected function addQuoteItemQtyErrorInfoFromResult(
        \Magento\Quote\Model\Quote\Item $quoteItem,
        \Magento\Framework\DataObject $result
    )
    {
        $this->addQuoteItemQtyErrorInfo($quoteItem, $result->getMessage());
        $this->addQuoteQtyErrorInfo($quoteItem, $result->getQuoteMessageIndex(), $result->getQuoteMessage());
        return $this;
    }

    /**
     * Remove quote item error infos
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param int $code
     * @return $this
     */
    protected function removeQuoteItemErrorInfos(\Magento\Quote\Model\Quote\Item $quoteItem, $code)
    {
        if ($quoteItem->getHasError()) {
            $quoteItem->removeErrorInfosByParams(['origin' => static::ERROR_ORIGIN, 'code' => $code]);
        }
        $quote = $quoteItem->getQuote();
        if (!$quote->getHasError()) {
            return $this;
        }
        $relatedQuoteItems = $quote->getItemsCollection();
        $removeQuoteErrorInfos = true;
        foreach ($relatedQuoteItems as $relatedQuoteItem) {
            if ($relatedQuoteItem->getItemId() == $quoteItem->getItemId()) {
                continue;
            }
            $errorInfos = $relatedQuoteItem->getErrorInfos();
            foreach ($errorInfos as $errorInfo) {
                if ($errorInfo['code'] == $code) {
                    $removeQuoteErrorInfos = false;
                    break;
                }
            }
            if (!$removeQuoteErrorInfos) {
                break;
            }
        }
        if ($removeQuoteErrorInfos) {
            $quote->removeErrorInfosByParams(null, ['origin' => static::ERROR_ORIGIN, 'code' => $code]);
        }
        return $this;
    }

    /**
     * Remove quote item qty error infos
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param int $code
     * @return $this
     */
    protected function removeQuoteItemQtyErrorInfos(\Magento\Quote\Model\Quote\Item $quoteItem)
    {
        return $this->removeQuoteItemErrorInfos($quoteItem, \Magento\CatalogInventory\Helper\Data::ERROR_QTY);
    }

    /**
     * Initialize
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initialize(\Magento\Quote\Model\Quote\Item $quoteItem)
    {
        $product = $quoteItem->getProduct();
        $stockRegistry = $this->getSubjectPropertyValue('stockRegistry');
        $optionInitializer = $this->getSubjectPropertyValue('optionInitializer');
        $stockItemInitializer = $this->getSubjectPropertyValue('stockItemInitializer');
        $stockItem = $stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        if (!$stockItem instanceof \Magento\CatalogInventory\Api\Data\StockItemInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The Product stock item is invalid. Verify the stock item and try again.'));
        }
        $qtyOptions = $quoteItem->getQtyOptions();
        $qty = $quoteItem->getQty();
        if ($qtyOptions && $qty > 0) {
            foreach ($qtyOptions as $qtyOption) {
                $optionInitializer->initialize($qtyOption, $quoteItem, $qty);
            }
        } else {
            $stockItemInitializer->initialize($stockItem, $quoteItem, $qty);
        }
        return $this;
    }

    /**
     * Is out of stock stock status
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface|null $stockStatus
     * @return bool
     */
    protected function isOutOfStockStockStatus($stockStatus): bool
    {
        return !!$stockStatus && $stockStatus->getStockStatus() == \Magento\CatalogInventory\Model\Stock::STOCK_OUT_OF_STOCK;
    }

    /**
     * Get quote item stock status
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    protected function getQuoteItemStockStatus(\Magento\Quote\Model\Quote\Item $quoteItem): \Magento\CatalogInventory\Api\Data\StockStatusInterface
    {
        $stockRegistry = $this->getSubjectPropertyValue('stockRegistry');
        $product = $quoteItem->getProduct();
        return $stockRegistry->getStockStatus($product->getId(), $product->getStore()->getId());
    }

    /**
     * Get quote item in stock stock status
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface|null
     */
    protected function getQuoteItemInStockStockStatus(\Magento\Quote\Model\Quote\Item $quoteItem): ?\Magento\CatalogInventory\Api\Data\StockStatusInterface
    {
        $stockStatus = $this->getQuoteItemStockStatus($quoteItem);
        $parentStockStatus = null;
        $parentQuoteItem = $quoteItem->getParentItem();
        if ($parentQuoteItem) {
            $parentStockStatus = $this->getQuoteItemStockStatus($parentQuoteItem);
        }
        if ($this->isOutOfStockStockStatus($stockStatus) || $this->isOutOfStockStockStatus($parentStockStatus)) {
            $this->addQuoteItemQtyErrorInfo($quoteItem, (string) __('This product is out of stock.'));
            $this->addQuoteQtyErrorInfo($quoteItem, 'stock', (string) __('Some of the products are out of stock Ecombricks.'));
            return null;
        } else {
            $this->removeQuoteItemQtyErrorInfos($quoteItem);
        }
        return $stockStatus;
    }

    /**
     * Around validate
     *
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function aroundValidate(
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    )
    {
        $this->setSubject($subject);
        $quoteItem = $observer->getEvent()->getItem();
        if (!$quoteItem || !$quoteItem->getProductId() || !$quoteItem->getQuote()) {
            return $this;
        }
        $this->initialize($quoteItem);
        if ($quoteItem->getQuote()->getIsSuperMode()) {
            return $this;
        }
        $stockStatus = $this->getQuoteItemInStockStockStatus($quoteItem);
        if ($stockStatus === null) {
            return $this;
        }
        $product = $quoteItem->getProduct();
        $qtyOptions = $quoteItem->getQtyOptions();
        if ($qtyOptions) {
            $quoteItem->setData('qty', $product->getTypeInstance()->prepareQuoteItemQty($quoteItem->getQty(), $product));
            $this->invokeSubjectMethod('checkOptionsQtyIncrements', $quoteItem, $qtyOptions);
            $removeError = true;
            foreach ($qtyOptions as $qtyOption) {
                $result = $qtyOption->getStockStateResult();
                if ($result->getHasError()) {
                    $qtyOption->setHasError(true);
                    $removeError = false;
                    $this->addQuoteItemQtyErrorInfoFromResult($quoteItem, $result);
                }
            }
            if ($removeError) {
                $this->removeQuoteItemQtyErrorInfos($quoteItem);
            }
        } else {
            if ($quoteItem->getParentItem() === null) {
                $result = $quoteItem->getStockStateResult();
                if ($result->getHasError()) {
                    $this->addQuoteItemQtyErrorInfoFromResult($quoteItem, $result);
                } else {
                    $this->removeQuoteItemQtyErrorInfos($quoteItem);
                }
            }
        }
    }

}
