<?php

namespace Amasty\StorePickupWithLocator\Plugin\Quote\Model;

use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\ShippingAddressManagement;

class ShippingAddressManagementPlugin
{
    /**
     * @param ShippingAddressManagement $subject
     * @param int $cartId
     * @param AddressInterface $address
     * @return array
     */
    public function beforeAssign(ShippingAddressManagement $subject, $cartId, AddressInterface $address)
    {
        if ($address->getLimitCarrier() === Shipping::SHIPPING_METHOD_CODE) {
            $address->setCustomerAddressId(null);
        }

        return [$cartId, $address];
    }
}
