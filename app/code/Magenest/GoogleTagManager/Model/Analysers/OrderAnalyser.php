<?php

namespace Magenest\GoogleTagManager\Model\Analysers;

class OrderAnalyser
{
    public function getGiftWrapQty(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $giftWrapItemsCount = \count(
            \array_filter(
                $order->getAllVisibleItems(),
                function (\Magento\Sales\Model\Order\Item $item) {
                    return $item->getGwId();
                }
            )
        );

        return $giftWrapItemsCount + (int)(bool)$order->getGwId();
    }

    public function getGiftWrapOrderTotalPrice(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $quotePrice = $order->getGwPrice();
        $quoteItemsPrice = $order->getGwItemsPrice();

        return $quotePrice + $quoteItemsPrice;
    }
}
