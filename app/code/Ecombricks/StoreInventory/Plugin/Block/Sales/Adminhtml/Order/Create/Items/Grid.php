<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Block\Sales\Adminhtml\Order\Create\Items;

/**
 * Create order items grid plugin
 */
class Grid extends \Ecombricks\Common\Plugin\Plugin
{

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
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState
    )
    {
        parent::__construct($wrapperFactory);
        $this->stockState = $stockState;
    }

    /**
     * Get quote item product IDs
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $quoteItem
     */
    protected function getQuoteItemProductIds(\Magento\Quote\Api\Data\CartItemInterface $quoteItem)
    {
        $productIds = [];
        $childQuoteItems = $quoteItem->getChildren();
        if (count($childQuoteItems)) {
            foreach ($childQuoteItems as $childQuoteItem) {
                $productIds[] = $childQuoteItem->getProduct()->getId();
            }
        } else {
            $productIds[] = $quoteItem->getProduct()->getId();
        }
        return $productIds;
    }

    /**
     * Around get items
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid $subject
     * @param \Closure $proceed
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function aroundGetItems(
        \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid $subject,
        \Closure $proceed
    )
    {
        $this->setSubject($subject);
        $quoteItems = $subject->getParentBlock()->getItems();
        $quote = $subject->getQuote();
        $oldSuperMode = $quote->getIsSuperMode();
        $quote->setIsSuperMode(false);
        $storeId = $quote->getStore()->getId();
        foreach ($quoteItems as $quoteItem) {
            $quoteItem->setQty($quoteItem->getQty());
            if (!$quoteItem->getMessage()) {
                foreach ($this->getQuoteItemProductIds($quoteItem) as $productId) {
                    $qty = $quoteItem->getQty();
                    $check = $this->stockState->checkQuoteItemQty($productId, $qty, $qty, $qty, $storeId);
                    $quoteItem->setMessage($check->getMessage());
                    $quoteItem->setHasError($check->getHasError());
                }
            }
            if ($quoteItem->getProduct()->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED) {
                $quoteItem->setMessage(__('This product is disabled.'));
                $quoteItem->setHasError(true);
            }
        }
        $quote->setIsSuperMode($oldSuperMode);
        return $quoteItems;
    }

}
