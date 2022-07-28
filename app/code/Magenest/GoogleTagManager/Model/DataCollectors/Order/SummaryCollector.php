<?php

namespace Magenest\GoogleTagManager\Model\DataCollectors\Order;

class SummaryCollector implements \Magenest\GoogleTagManager\Api\OrderInfoCollectorInterface
{
    /**
     * @var \Magenest\GoogleTagManager\Model\Checkout
     */
    private $checkout;

    public function __construct(
        \Magenest\GoogleTagManager\Model\Checkout $checkout
    ) {
        $this->checkout = $checkout;
    }

    public function collect(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        return [
            'id' => $order->getIncrementId(),
            'affiliation' => $order->getStoreGroupName(),
            'tax' => $order->getTaxAmount(),
            'revenue' => $this->checkout->getRevenue($order),
            'shipping' => $this->checkout->getShipping($order),
            'coupon' => $order->getCouponCode() ?: null,
        ];
    }
}
