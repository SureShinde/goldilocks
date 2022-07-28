<?php

namespace Magenest\GoogleTagManager\Api;

interface OrderInfoCollectorInterface
{
    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     */
    public function collect(\Magento\Sales\Api\Data\OrderInterface $order);
}
