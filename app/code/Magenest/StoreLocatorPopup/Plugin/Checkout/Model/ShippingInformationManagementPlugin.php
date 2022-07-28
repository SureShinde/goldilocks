<?php
namespace Magenest\StoreLocatorPopup\Plugin\Checkout\Model;

use Magenest\StoreLocatorPopup\Helper\Data as Datahelper;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;

class ShippingInformationManagementPlugin
{
    /** @var Datahelper  */
    protected $helperData;

    /**
     * @param Datahelper $helperData
     */
    public function __construct(
        Datahelper $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param ShippingInformationManagementInterface $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     *
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $address = $addressInformation->getShippingAddress();
        $addressFrom = $address->getStreetFull() . ", " . $address->getCity() . ", " . $address->getRegion() . ", " . $address->getPostcode() . ", " . $address->getCountry();
        $this->helperData->calculateDistance($addressFrom);
        return [$cartId, $addressInformation];
    }
}
