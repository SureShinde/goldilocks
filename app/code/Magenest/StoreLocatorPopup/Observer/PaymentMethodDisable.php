<?php

namespace Magenest\StoreLocatorPopup\Observer;

use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;
use Magenest\StoreLocatorPopup\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;

class PaymentMethodDisable implements ObserverInterface
{
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @param Data $dataHelper
     */
    public function __construct(
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->dataHelper->getEnabledRestrict()) {
            return;
        }
        $paymentMethodRestrictList = explode(',', $this->dataHelper->getPaymentMethodRestrict());
        /* @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');
        $paymentMethod = $observer->getEvent()->getData('method_instance')->getCode();
        if ($quote && $quote->getShippingAddress()
            && $quote->getShippingAddress()->getShippingMethod() == Shipping::SHIPPING_NAME
            && in_array($paymentMethod, $paymentMethodRestrictList)
        ) {
            $checkResult = $observer->getEvent()->getData('result');
            $checkResult->setData('is_available', false);
        }
    }
}
