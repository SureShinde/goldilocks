<?php

namespace Amasty\DeliveryDateManager\Plugin\Checkout\Model;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Model\ShippingAddressManagementInterface;

/**
 * Validate and save delivery date extension attributes
 */
class ShippingInformationManagementPlugin
{
    /**
     * @var ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;

    /**
     * @var \Amasty\DeliveryDateManager\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Amasty\DeliveryDateManager\Model\DeliveryQuote\Manager
     */
    private $deliveryQuoteManager;

    public function __construct(
        ShippingAddressManagementInterface $shippingAddressManagement,
        \Amasty\DeliveryDateManager\Model\ConfigProvider $configProvider,
        \Amasty\DeliveryDateManager\Model\DeliveryQuote\Manager $deliveryQuoteManager
    ) {
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->configProvider = $configProvider;
        $this->deliveryQuoteManager = $deliveryQuoteManager;
    }

    /**
     * Save delivery date data
     *
     * @param ShippingInformationManagement $subject
     * @param \Magento\Checkout\Api\Data\PaymentDetailsInterface $paymentDetails
     * @param string|int $cartId
     * @param ShippingInformationInterface $addressInformation
     *
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     */
    public function afterSaveAddressInformation(
        ShippingInformationManagement $subject,
        $paymentDetails,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        if ($this->configProvider->isEnabled()) {
            $addressId = $this->shippingAddressManagement->get($cartId)->getId();
            $extensionAttributes = $addressInformation->getExtensionAttributes();

            if (!$extensionAttributes) {
                return $paymentDetails;
            }

            $timeId = null;
            if ($extensionAttributes->getAmdeliverydateTimeId()) {
                $timeId = (int)$extensionAttributes->getAmdeliverydateTimeId();
            }

            $this->deliveryQuoteManager->saveForQuoteAddress(
                $cartId,
                $addressId,
                $extensionAttributes->getAmdeliverydateDate(),
                $timeId,
                $extensionAttributes->getAmdeliverydateComment()
            );
        }

        return $paymentDetails;
    }
}
