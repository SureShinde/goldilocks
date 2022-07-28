<?php

namespace Magenest\GoogleTagManager\Plugin\Quote\Item;

use Magento\Quote\Model\Quote\Item\Compare;
use Magenest\GoogleTagManager\Model\Quote\Item\PurchaseCategory;

class IgnorePurchaseCategoryOnCompare
{
    /**
     * Ignores purchase_category when comparing two quotes for merging
     *
     * Magento will not merge otherwise identical quote items if the purchase category is different,
     * so we exclude this field from the comparison
     *
     * @param Compare $subject
     * @param array $options
     * @return array
     */
    public function afterGetOptions(Compare $subject, array $options)
    {
        unset($options['info_buyRequest'][PurchaseCategory::PURCHASE_CATEGORY]);

        return $options;
    }
}
