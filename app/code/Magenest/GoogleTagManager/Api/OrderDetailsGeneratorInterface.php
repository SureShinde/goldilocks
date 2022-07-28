<?php

namespace Magenest\GoogleTagManager\Api;

interface OrderDetailsGeneratorInterface
{
    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     */
    public function generate(\Magento\Sales\Api\Data\OrderInterface $order);
}
