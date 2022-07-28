<?php

namespace Magenest\SpecialCustomerProgram\Plugin\Model;

use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Model\Rule;

class Utility
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * Check if rule can be applied for specific address/quote/customer
     *
     * @param \Magento\SalesRule\Model\Utility $subject
     * @param bool $result
     * @param Rule $rule
     * @param Address $address
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function afterCanProcessRule(
        \Magento\SalesRule\Model\Utility $subject,
        $result,
        Rule                             $rule,
        Address                          $address
    ) {
        $quote = $address->getQuote();
        if (!$quote->getData('special_customer_program') && $rule->getData('special_customer_program')) {
            return false;
        }
        if ($rule->getData('discount_with_first_purchase')) {
            $customerId = $quote->getCustomerId();
            if (!$customerId || !$this->validateFirstPurchase($customerId)) {
                return false;
            }
        }
        return $result;
    }

    /**
     * @param $customerId
     * @return bool
     */
    private function validateFirstPurchase($customerId): bool
    {
        return $this->orderCollectionFactory->create()
            ->addAttributeToFilter('customer_id', $customerId)->getSize() == 0;
    }
}
