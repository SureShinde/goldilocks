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
namespace Bss\ShoppingCartRulePerStoreView\Model\ResourceModel\Rule;

class CollectionPlugin extends \Magento\SalesRule\Model\ResourceModel\Rule\Collection
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Backend\Model\Session\QuoteFactory
     */
    protected $sessionFactory;

    /**
     * Collection constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Backend\Model\Session\QuoteFactory $sessionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $state,
        \Magento\Backend\Model\Session\QuoteFactory $sessionFactory
    ) {
        $this->request = $context->getRequest();
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\Collection $subject
     * @param mixed $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAddWebsiteGroupDateFilter($subject, $result)
    {
        if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $storeIds = [(int) $this->sessionFactory->create()->getStoreId()];
        } else {
            $storeIds = [(int) $this->storeManager->getStore()->getId()];
        }

        $entityInfo = $result->_getAssociatedEntityInfo('store');

        $result->getSelect()->join(
            ['store' => $result->getTable($entityInfo['associations_table'])],
            $result->getConnection()->quoteInto('store.' . $entityInfo['entity_id_field'] . ' IN (?)', $storeIds)
            . ' AND main_table.' . $entityInfo['rule_id_field'] . ' = store.' . $entityInfo['rule_id_field'],
            []
        );
        return $result;
    }
}
