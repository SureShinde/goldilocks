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

namespace Mageplaza\ShippingRestriction\Model\Rule\Condition;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Combine as CombineSalesRule;
use Magento\SalesRule\Model\Rule\Condition\Product\Found;
use Magento\SalesRule\Model\Rule\Condition\Product\Subselect;

/**
 * Class Combine
 * @package Mageplaza\ShippingRestriction\Model\Rule\Condition
 */
class Combine extends CombineSalesRule
{
    /**
     * @var Attribute
     */
    protected $customerAttribute;

    /**
     * Combine constructor.
     *
     * @param Context $context
     * @param ManagerInterface $eventManager
     * @param Address $conditionAddress
     * @param Attribute $customerAttribute
     * @param array $data
     */
    public function __construct(
        Context $context,
        ManagerInterface $eventManager,
        Address $conditionAddress,
        Attribute $customerAttribute,
        array $data = []
    ) {
        $this->customerAttribute = $customerAttribute;

        parent::__construct($context, $eventManager, $conditionAddress, $data);
        $this->setType(__CLASS__);
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $addressAttributes  = $this->_conditionAddress->loadAttributeOptions()->getAttributeOption();
        $customerAttributes = $this->customerAttribute->loadAttributeOptions()->getAttributeOption();
        $attributesAddress  = [];
        $attributesCustomer = [];
        foreach ($addressAttributes as $code => $label) {
            $attributesAddress[] = [
                'value' => Address::class . '|' . $code,
                'label' => $label,
            ];
        }

        foreach ($customerAttributes as $code => $label) {
            $attributesCustomer[] = [
                'value' => Attribute::class . '|' . $code,
                'label' => $label,
            ];
        }

        $conditions = [['value' => '', 'label' => __('Please choose a condition to add.')]];
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => Found::class,
                    'label' => __('Product attribute combination'),
                ],
                [
                    'value' => Subselect::class,
                    'label' => __('Products subselection')
                ],
                [
                    'value' => CombineSalesRule::class,
                    'label' => __('Conditions combination')
                ],
                ['label' => __('Cart Attribute'), 'value' => $attributesAddress],
                ['label' => __('Customer Attribute'), 'value' => $attributesCustomer]
            ]
        );

        $additional = new DataObject();
        $this->_eventManager->dispatch('salesrule_rule_condition_combine', ['additional' => $additional]);
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }
}
