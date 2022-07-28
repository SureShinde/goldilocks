<?php

namespace Magenest\GoogleTagManager\Observer\ProductObject;

use Magento\Framework\DataObject;
use Magenest\GoogleTagManager\Model\Quote\Item\PurchaseCategory;

class SetQuoteItemPurchasedCategory implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magenest\GoogleTagManager\Helper\Data
     */
    private $dataHelper;

    /**
     * @var PurchaseCategory
     */
    private $purchaseCategory;

    public function __construct(
        PurchaseCategory $purchaseCategory,
        \Magenest\GoogleTagManager\Helper\Data $dataHelper
    ) {
        $this->purchaseCategory = $purchaseCategory;
        $this->dataHelper = $dataHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->dataHelper->isEnabled()) {
            return;
        }

        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $observer->getData('quote_item');

        /** @var DataObject $data */
        $data = $observer->getData('data');

        $category = $this->purchaseCategory->get($quoteItem);

        if ($category) {
            $data->setData('category', $category);
        }
    }
}
