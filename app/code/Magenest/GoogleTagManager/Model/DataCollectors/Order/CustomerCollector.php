<?php

namespace Magenest\GoogleTagManager\Model\DataCollectors\Order;

class CustomerCollector implements \Magenest\GoogleTagManager\Api\OrderInfoCollectorInterface
{
    /**
     * @var \Magenest\GoogleTagManager\Model\Customer\Context
     */
    private $customerContext;

    public function __construct(
        \Magenest\GoogleTagManager\Model\Customer\Context $customerContext
    ) {
        $this->customerContext = $customerContext;
    }

    public function collect(\Magento\Sales\Api\Data\OrderInterface $order) // phpcs:ignore VCQP.CodeAnalysis.UnusedFunctionParameter.Found
    {
        return [
            'customerGroup' => $this->customerContext->getGroupCode(),
        ];
    }
}
