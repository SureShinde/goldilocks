<?php

namespace Magenest\Checkout\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magenest\Directory\Helper\Data;

/**
 * Class LayoutProcessor
 * @package Magenest\Directory\Block\Checkout
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $shippingAddressFieldset = $jsLayout['components']['checkout']['children']['steps']['children']
        ['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

        $shippingAddressFieldset['company']['visible'] = false;
        $shippingAddressFieldset['region_id']['sortOrder'] = 80;
        $shippingAddressFieldset['region_id']['label'] = __("Barangay");
        $shippingAddressFieldset['city']['label'] = __("Province/City");
        $shippingAddressFieldset['city']['sortOrder'] = 90;
        $shippingAddressFieldset['country_id']['sortOrder'] = 100;

        $jsLayout['components']['checkout']['children']['steps']['children']
        ['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] = $shippingAddressFieldset;
        return $jsLayout;
    }


}
