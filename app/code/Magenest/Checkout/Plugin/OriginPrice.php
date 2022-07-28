<?php

namespace Magenest\Checkout\Plugin;

use Magento\Checkout\CustomerData\AbstractItem;
use Magento\Checkout\Helper\Data;

class OriginPrice
{
    /**
     * @var Data
     */
    protected $checkoutHelper;

    /**
     * @param Data $checkoutHelper
     */
    public function __construct(Data $checkoutHelper)
    {
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * @param AbstractItem $subject
     * @param $result
     * @param $item
     * @return mixed
     */
    public function afterGetItemData(AbstractItem $subject, $result, $item)
    {
        $regularPrice = $item->getProduct()->getPriceInfo()->getPrice('regular_price')->getValue();
        $finalPriceAmt = $item->getProduct()->getPriceInfo()->getPrice('final_price')->getValue();
        if ($finalPriceAmt < $regularPrice) {
            $result['product_origin_price'] = $this->checkoutHelper->formatPrice($regularPrice);
            return $result;
        }
        return $result;
    }

}
