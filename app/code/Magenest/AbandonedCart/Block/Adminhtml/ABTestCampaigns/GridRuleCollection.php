<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaigns;

use Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaigns\Edit\Tab\GridRule;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;
use Magenest\AbandonedCart\Model\ABTestCampaignFactory;
use Magenest\AbandonedCart\Model\ResourceModel\ABTestCampaignFactory as ABTestCampaignResource;
use Magenest\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class GridRuleCollection
 * @package Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaign
 */
class GridRuleCollection extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     * @var string
     */
    protected $_template = 'abtestcampaigns/edit/grid_rule.phtml';

    /**
     * @var GridRule
     */
    protected $blockGrid;

    /**
     * @var ABTestCampaignFactory $_aBTestCampaignModel
     */
    protected $_aBTestCampaignModel;

    /**
     * @var ABTestCampaignResource $_aBTestCampaignResource
     */
    protected $_aBTestCampaignResource;

    /** @var RuleCollection $_ruleCollection */
    protected $_ruleCollection;

    /**
     * @var SerializerInterface
     */
    protected $serializer;


    /**
     * GridRuleCollection constructor.
     * @param Context $context
     * @param ABTestCampaignFactory $ABTestCampaignModel
     * @param ABTestCampaignResource $ABTestCampaignResource
     * @param RuleCollection $ruleCollection
     * @param SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        ABTestCampaignFactory $ABTestCampaignModel,
        ABTestCampaignResource $ABTestCampaignResource,
        RuleCollection $ruleCollection,
        SerializerInterface $serializer,
        array $data = []
    ) {
        $this->_aBTestCampaignModel = $ABTestCampaignModel->create();
        $this->_aBTestCampaignResource = $ABTestCampaignResource->create();
        $this->_ruleCollection = $ruleCollection;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * @return Edit\Tab\GridRule|BlockInterface
     * @throws LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                GridRule::class,
                'collection.rule.grid'
            );
        }

        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     * @return string
     * @throws LocalizedException
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }


    /**
     * @return \Magenest\AbandonedCart\Model\ABTestCampaign|ABTestCampaignFactory
     */
    public function getDataCampaign()
    {
        if (!$this->_aBTestCampaignModel->getId()) {
            $id = $this->getRequest()->getParam('id');
            if ($id != null) {
                $this->_aBTestCampaignResource->load($this->_aBTestCampaignModel, $id, 'id');
            }
        }

        return $this->_aBTestCampaignModel;
    }

    /**
     * @return bool|string|null
     * @throws LocalizedException
     */
    public function getDataCollection()
    {
        if (null !== $this->blockGrid) {
            if ($this->getBlockGrid()->getCollection()->count() > 0) {
                return $this->serializer->serialize($this->getBlockGrid()->getCollection()->getData());
            }
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isDisplay()
    {
        if ($this->getRequest()->getParam('id')) {
            return true;
        }
        return false;
    }
}
