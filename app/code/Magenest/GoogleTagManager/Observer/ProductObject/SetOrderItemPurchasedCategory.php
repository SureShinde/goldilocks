<?php

namespace Magenest\GoogleTagManager\Observer\ProductObject;

use Magento\Framework\DataObject;
use Magenest\GoogleTagManager\Model\Order\Item\PurchaseCategory;

class SetOrderItemPurchasedCategory implements \Magento\Framework\Event\ObserverInterface
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

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $observer->getData('order_item');

        /** @var DataObject $data */
        $data = $observer->getData('data');

        $category = $this->purchaseCategory->get($orderItem);

        if ($category) {
            $data->setData('category', $category);
        }
    }
}
