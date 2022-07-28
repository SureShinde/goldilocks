<?php

namespace Magenest\GoogleTagManager\Model\DataCollectors\Order;

class GiftWrapCollector implements \Magenest\GoogleTagManager\Api\OrderInfoCollectorInterface
{
    /**
     * @var \Magenest\GoogleTagManager\Model\Analysers\OrderAnalyser
     */
    private $orderAnalyser;

    public function __construct(
        \Magenest\GoogleTagManager\Model\Analysers\OrderAnalyser $orderAnalyser
    ) {
        $this->orderAnalyser = $orderAnalyser;
    }

    public function collect(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $giftWrapQty = $this->orderAnalyser->getGiftWrapQty($order);

        return [
            'giftWrapQty' => $giftWrapQty,
            'giftWrapPrice' => $giftWrapQty
                ? $this->orderAnalyser->getGiftWrapOrderTotalPrice($order)
                : 0,
        ];
    }
}
