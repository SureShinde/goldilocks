<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at thisURL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ShoppingCartRulePerStoreView
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ShoppingCartRulePerStoreView\Plugin;

class Coupon
{
    /**
     * @var \Magento\SalesRule\Model\RuleRepository
     */
    protected $ruleRepository;

    /**
     * @var \Bss\ShoppingCartRulePerStoreView\Model\ResourceModel\Rule
     */
    protected $ruleResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\SalesRule\Model\RuleRepository $ruleRepository
     * @param \Bss\ShoppingCartRulePerStoreView\Model\ResourceModel\Rule $ruleResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\SalesRule\Model\RuleRepository $ruleRepository,
        \Bss\ShoppingCartRulePerStoreView\Model\ResourceModel\Rule $ruleResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->ruleResource = $ruleResource;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\SalesRule\Model\Coupon $subject
     * @param \Magento\SalesRule\Model\Coupon $result
     * @return \Magento\SalesRule\Model\Coupon
     */
    public function afterAfterLoad($subject, $result)
    {
        try {
            $rule = $this->ruleRepository->getById($subject->getRuleId());
            $ruleStoreIds = $this->ruleResource->getStoreIds($rule->getRuleId());

            $storeId = $this->storeManager->getStore()->getId();
            if (!in_array($storeId, $ruleStoreIds)) {
                $result->setId('');
            }
        } catch (\Exception $e) {
            $result->setId('');
        }
        
        return $result;
    }
}
