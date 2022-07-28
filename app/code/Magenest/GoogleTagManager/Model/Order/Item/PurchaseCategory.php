<?php

namespace Magenest\GoogleTagManager\Model\Order\Item;

class PurchaseCategory
{
    const PURCHASE_CATEGORY = \Magenest\GoogleTagManager\Model\Quote\Item\PurchaseCategory::PURCHASE_CATEGORY;

    public function get(\Magento\Sales\Model\Order\Item $item)
    {
        return $item->getBuyRequest()->getData(self::PURCHASE_CATEGORY);
    }
}
