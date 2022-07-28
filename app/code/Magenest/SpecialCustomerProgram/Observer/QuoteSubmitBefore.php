<?php

namespace Magenest\SpecialCustomerProgram\Observer;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class QuoteSubmitBefore implements ObserverInterface
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        CustomerFactory $customerFactory
    ) {
        $this->customerFactory = $customerFactory;

    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getQuote();
        $order = $observer->getOrder();
        if ($order->getCustomerId()) {
            $customer = $this->customerFactory->create()->load($order->getCustomerId());
            $dataModel = $customer->getDataModel();
            $dataModel->setCustomAttribute('ci_number', $quote->getData('ci_number'));
            $dataModel->setCustomAttribute('ci_full_name', $quote->getData('ci_full_name'));
            $dataModel->setCustomAttribute('ci_image', $quote->getData('ci_image'));
            $customer->updateData($dataModel);
            $customer->save();
        }
        $order->setData('special_customer_program', $quote->getData('special_customer_program'));
        $order->setData('ci_number', $quote->getData('ci_number'));
        $order->setData('ci_full_name', $quote->getData('ci_full_name'));
        $order->setData('ci_image', $quote->getData('ci_image'));
    }
}
