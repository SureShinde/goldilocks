<?php

namespace Magenest\GoogleTagManager\Model\Quote\Item;

class PurchaseCategory
{
    const PURCHASE_CATEGORY = 'purchase_category';

    public function get(\Magento\Quote\Model\Quote\Item $item)
    {
        return $item->getBuyRequest()->getData(self::PURCHASE_CATEGORY);
    }
}
