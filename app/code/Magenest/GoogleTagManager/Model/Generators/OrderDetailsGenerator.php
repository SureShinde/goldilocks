<?php

namespace Magenest\GoogleTagManager\Model\Generators;

use Magenest\GoogleTagManager\Api\OrderInfoCollectorInterface as Collector;

class OrderDetailsGenerator implements \Magenest\GoogleTagManager\Api\OrderDetailsGeneratorInterface
{
    /**
     * @var \Magenest\GoogleTagManager\Helper\DataCollector
     */
    private $dataCollectorHelper;

    /**
     * @var \Magenest\GoogleTagManager\Api\OrderInfoCollectorInterface[]
     */
    private $dataCollectors;

    /**
     * @param \Magenest\GoogleTagManager\Helper\DataCollector $dataCollectorHelper
     * @param \Magenest\GoogleTagManager\Api\OrderInfoCollectorInterface[] $dataCollectors
     */
    public function __construct(
        \Magenest\GoogleTagManager\Helper\DataCollector $dataCollectorHelper,
        array $dataCollectors
    ) {
        $this->dataCollectorHelper = $dataCollectorHelper;
        $this->dataCollectors = $dataCollectors;
    }

    public function generate(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        return $this->dataCollectorHelper->walkCollectors(
            $this->dataCollectors,
            function (Collector $collector) use ($order) {
                return $collector->collect($order);
            }
        );
    }
}
