<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ShippingRestriction
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingRestriction\Plugin\Checkout;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Multishipping\Block\Checkout\Shipping;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Mageplaza\ShippingRestriction\Model\Config\Source\Location;
use Mageplaza\ShippingRestriction\Model\ResourceModel\Rule\Collection;
use Mageplaza\ShippingRestriction\Plugin\Model\Quote\Address;

/**
 * Class MultiShipping
 * @package Mageplaza\ShippingRestriction\Plugin\Model
 */
class MultiShipping extends Address
{
    /**
     * @param Shipping $subject
     * @param callable $proceed
     * @param QuoteAddress $address
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function aroundGetShippingRates(Shipping $subject, callable $proceed, $address)
    {
        $this->appliedRule = [];
        $quote             = $subject->getCheckout()->getQuote();
        $groups            = $proceed($address);

        if ($quote->getId() && $this->_helperData->isEnabled($this->_store->getStore()->getId())) {
            /** @var Collection $ruleCollection */
            $ruleCollection = $this->_helperData->getShippingRuleCollection();
            /** @var Quote $quote */
            $quote          = $this->_cartRepository->getActive($quote->getId());

            $this->getFrontendAppliedRule($quote, $address, $ruleCollection, true);
            if (!empty($this->appliedRule)) {
                $shippingMethodsApplied = [];
                foreach ($this->appliedRule as $rule) {
                    if (!$rule) {
                        continue;
                    }
                    $appliedShipMethod = $rule->getShippingMethods();
                    $appliedShipMethod = explode(',', $appliedShipMethod);
                    $locations         = explode(',', $rule->getLocation());

                    if (in_array((string)Location::ORDER_FRONTEND, $locations, true)) {
                        foreach ($appliedShipMethod as $shippingCode) {
                            if (!isset($shippingMethodsApplied[$shippingCode])) {
                                $shippingMethodsApplied[$shippingCode] = $rule->getAction();
                            }
                        }
                    }
                }
                if ($shippingMethodsApplied) {
                    if (count(array_unique($shippingMethodsApplied)) === 1) {
                        $shippingMethodsApplied['only_action'] = true;
                    } else {
                        $shippingMethodsApplied['only_action'] = false;
                    }
                    $this->processShippingMethod($groups, $shippingMethodsApplied);
                }
            }
        }

        return $groups;
    }
}
