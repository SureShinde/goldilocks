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

namespace Mageplaza\ShippingRestriction\Plugin\Adminhtml\Model\Quote;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Mageplaza\ShippingRestriction\Model\Config\Source\Location;
use Mageplaza\ShippingRestriction\Model\ResourceModel\Rule\Collection;
use Mageplaza\ShippingRestriction\Model\Rule;
use Mageplaza\ShippingRestriction\Plugin\ShippingRestrictionPlugin;

/**
 * Class Address
 * @package Mageplaza\ShippingRules\Plugin\Adminhtml\Model\Quote
 */
class Address extends ShippingRestrictionPlugin
{
    /**
     * @param QuoteAddress $subject
     * @param array $result
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterGetGroupedAllShippingRates(
        QuoteAddress $subject,
        $result
    ) {
        $this->ruleActive = false;
        $shippingRatesCol = $result;

        /** @var Quote $quote */
        $quote          = $this->_quoteSession->getQuote();
        /** @var Collection $ruleCollection */
        $ruleCollection = $this->_helperData->getShippingRuleCollection($quote->getCustomerGroupId());
        $cartId         = $quote->getId();

        if ($cartId && $this->_helperData->isEnabled()) {
            $appliedSaleRuleIds = $this->_backendSession->getData('mp_shipping_restriction_applied_rule_ids');
            if (!is_array($appliedSaleRuleIds)) {
                $appliedSaleRuleIds = [];
            }
            $currentWebsiteId = $quote->getStore()->getWebsiteId();
            $this->getBackendAppliedRule($quote, $ruleCollection, $appliedSaleRuleIds, $currentWebsiteId);

            if (!empty($this->appliedRule)) {
                $shippingMethodsApplied = [];
                foreach ($this->appliedRule as $rule) {
                    if (!$rule) {
                        continue;
                    }
                    $appliedShipMethod = $rule->getShippingMethods();
                    $appliedShipMethod = explode(',', $appliedShipMethod);
                    $locations         = explode(',', $rule->getLocation());

                    if (in_array((string)Location::ORDER_BACKEND, $locations, true)) {
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
                    $this->processShippingMethod($shippingRatesCol, $shippingMethodsApplied);
                }
            }
        }

        return $shippingRatesCol;
    }

    /**
     * @param Quote $quote
     * @param Collection $ruleCollection
     * @param array $appliedSaleRuleIds
     * @param int $currentWebsite
     *
     * @throws NoSuchEntityException
     */
    public function getBackendAppliedRule($quote, $ruleCollection, $appliedSaleRuleIds, $currentWebsite)
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);

        /** @var Rule $rule */
        foreach ($ruleCollection as $rule) {
            if (!$this->_helperData->isInScheduled($rule, $currentWebsite)) {
                continue;
            }
            if ($rule->getSaleRulesInactive()) {
                $saleRuleInactive = explode(',', $rule->getSaleRulesInactive());
                if (array_intersect($saleRuleInactive, $appliedSaleRuleIds)) {
                    $this->appliedRule[] = null;
                    continue;
                }
            }
            if ($rule->getSaleRulesActive()) {
                $saleRuleActive = explode(',', $rule->getSaleRulesActive());
                if (array_intersect($saleRuleActive, $appliedSaleRuleIds)) {
                    $this->appliedRule[] = $rule;
                    if ($rule->getDiscardSubRule()) {
                        break;
                    }
                }
                continue;
            }
            if ($rule->validate($shippingAddress)) {
                $this->appliedRule[] = $rule;
                if ($rule->getDiscardSubRule()) {
                    break;
                }
            }
        }
    }
}
