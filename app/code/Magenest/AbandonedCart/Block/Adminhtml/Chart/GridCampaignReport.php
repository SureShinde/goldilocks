<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\AbandonedCart\Block\Adminhtml\Chart;

use Magenest\AbandonedCart\Block\Adminhtml\Chart\Edit\Tab\CampaignGrid;
use Magenest\AbandonedCart\Model\AbandonedCart as AbandonedModel;
use Magenest\AbandonedCart\Model\Config\Source\Mail;
use Magenest\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;
use Magenest\AbandonedCart\Model\ABTestCampaignFactory;
use Magenest\AbandonedCart\Model\ResourceModel\ABTestCampaign as ABTestCampaignResource;
use Magenest\AbandonedCart\Model\ResourceModel\ABTestCampaign\CollectionFactory as CampaignCollection;
use Magento\Framework\Serialize\Serializer\Json;
use Magenest\AbandonedCart\Model\ResourceModel\LogContent\CollectionFactory as LogContentCollection;
use Magenest\AbandonedCart\Model\ResourceModel\AbandonedCart\CollectionFactory as AbandonedCollection;
use Magenest\AbandonedCart\Model\LogContentFactory;
use Magenest\AbandonedCart\Helper\Data as AbandonedHelper;
use Magenest\AbandonedCart\Model\Rule as AbandonedRule;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollection;

/**
 * Class GridRuleCollection
 * @package Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaign
 */
class GridCampaignReport extends \Magento\Backend\Block\Template
{
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

    /** @var CampaignCollection $_campaignCollection */
    protected $_campaignCollection;

    /** @var Json */
    protected $serializer;

    /** @var Data $_helperData */
    protected $_helperData;

    /** @var RuleCollection $_ruleCollection */
    protected $_ruleCollection;

    /** @var LogContentCollection $_logCollection */
    protected $_logCollection;

    /** @var AbandonedCollection $_abandonedCollection */
    protected $_abandonedCollection;

    /** @var LogContentFactory $_logContentFactory */
    protected $_logContentFactory;

    /** @var AbandonedHelper $_abandonedHelper */
    protected $_abandonedHelper;

    /** @var QuoteCollection  */
    protected $_quoteCollection;

    /**
     * GridRuleCollection constructor.
     * @param Context $context
     * @param ABTestCampaignFactory $ABTestCampaignModel
     * @param ABTestCampaignResource $ABTestCampaignResource
     * @param CampaignCollection $campaignCollection
     * @param Json $serializer
     * @param Data $helperData
     * @param RuleCollection $ruleCollection
     * @param LogContentCollection $logCollection
     * @param AbandonedCollection $abandonedCollection
     * @param LogContentFactory $logContentFactory
     * @param AbandonedHelper $abandonedHelper
     * @param QuoteCollection $quoteCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        ABTestCampaignFactory $ABTestCampaignModel,
        ABTestCampaignResource $ABTestCampaignResource,
        CampaignCollection $campaignCollection,
        Json $serializer,
        Data $helperData,
        RuleCollection $ruleCollection,
        LogContentCollection $logCollection,
        AbandonedCollection $abandonedCollection,
        LogContentFactory $logContentFactory,
        AbandonedHelper $abandonedHelper,
        QuoteCollection $quoteCollection,
        array $data = []
    ) {
        $this->_aBTestCampaignModel = $ABTestCampaignModel->create();
        $this->_aBTestCampaignResource = $ABTestCampaignResource;
        $this->_campaignCollection = $campaignCollection;
        $this->serializer = $serializer;
        $this->_helperData = $helperData;
        $this->_ruleCollection = $ruleCollection;
        $this->_logCollection = $logCollection;
        $this->_abandonedCollection = $abandonedCollection;
        $this->_logContentFactory = $logContentFactory;
        $this->_abandonedHelper = $abandonedHelper;
        $this->_quoteCollection = $quoteCollection;
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
                CampaignGrid::class,
                'grid.campaign.report'
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
     * @return bool|string
     */
    public function getDataDrawChart()
    {
        $data = $this->getRequest()->getParams();
        if (isset($data['from']) && isset($data['to']) && isset($data['id_campaign'])) {
            $this->_aBTestCampaignResource->load($this->_aBTestCampaignModel, $data['id_campaign'], 'id');
            $fromDate = date('y-m-d', strtotime($data['from']));
            $toDate = date('y-m-d', strtotime($data['to']));
            $totalProductAbandoned = $this->getTotalProductAbandoned($fromDate, $toDate);
            $totalRecoveredProduct = $this->getTotalRecoveredProduct($fromDate, $toDate);
            /** @var RuleCollection $collection */
            /** collection data for show in grid */
            $collection = $this->_ruleCollection->create()
                ->addFieldToFilter('from_date', ['gteq' => $fromDate])
                ->addFieldToFilter('to_date', ['lteq' => $toDate])
                ->addFieldToFilter('status',AbandonedRule::RULE_ACTIVE)
                ->addFilterToMap('id', 'main_table.id');
            $emailSent = 0;
            $opened = 0;
            $clicks = 0;
            foreach ($this->_abandonedHelper->getCollectionRule($collection) as $campaign) {
                $emailSent += $campaign->getSent();
                $opened += $campaign->getOpened();
                $clicks += $campaign->getClicks();
            }
            return $this->serializer->serialize([
                'message' => true,
                'total_sent' => $emailSent,
                'total_opened' => $opened,
                'total_unopened' => $emailSent > $opened ? $emailSent - $opened : 0,
                'total_clicks' => $clicks,
                'total_unclicked' => $opened > $clicks ? $opened - $clicks : 0,
                'total_recovered_cart' => $this->getRepurchasedAbandonedCarts($fromDate, $toDate),
                'total_unrecovered_cart' => $this->getNonRepurchasedAbandonedCarts($fromDate, $toDate),
                'total_recovered_product' => $totalRecoveredProduct,
                'total_unrecovered_product' => $totalProductAbandoned - $totalRecoveredProduct,
                'name_campaign' => $this->_aBTestCampaignModel->getData('name')
            ]);
        }
        return $this->serializer->serialize(['message'=>false]);
    }

    /**
     * @return bool|false|string
     * @throws LocalizedException
     */
    public function getDataCollection()
    {
        if (null !== $this->blockGrid) {
            if ($this->getBlockGrid()->getCollection()->count() > 0) {
                return $this->serializer->serialize($this->getBlockGrid()->getCollection()->getData());
            }
        }
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getTotalABTestCampaign()
    {
        return $this->getBlockGrid()->getCollection()->count();
    }

    /**
     * @return int
     */
    public function getTotalOnRun()
    {
        $dataFilter = $this->getRequest()->getParams();
        $total = 0;
        if (isset($dataFilter['from']) || isset($dataFilter['to'])) {
            $fromDate = date('y-m-d', strtotime($dataFilter['from']));
            $toDate = date('y-m-d', strtotime($dataFilter['to']));
            $collection = $this->_campaignCollection->create()
                ->addFieldToFilter('main_table.status', 1)
                ->addFieldToFilter('from_date', ['gteq' => $fromDate])
                ->addFieldToFilter('from_date', ['lteq' => $toDate]);
            $total = $collection->count();
        } else {
            $collection = $this->_campaignCollection->create()
                ->addFieldToFilter('main_table.status', 1);
            $collection->getSelect()->where('DAYOFYEAR(to_date) >= DAYOFYEAR(NOW())');
            $total = $collection->count();
        }
        return $total;
    }

    /**
     * @param $from
     * @param $to
     * @return int|void
     */
    public function getTotalAbandonedCart($from, $to)
    {
        $logContent = $this->_logCollection->create();
        $logContentCollection = $logContent->addFieldToFilter('main_table.created_at', ['gteq' => $from])
            ->addFieldToFilter('main_table.created_at', ['lteq' => $to])
            ->addFieldToFilter('status', AbandonedModel::STATUS_ABANDONED);
        $rows = $logContent->getResource()->getConnection()->fetchAll($logContentCollection);
        return count($rows);
    }

    /**
     * @param $from
     * @param $to
     * @return int|mixed
     */
    public function getTotalProductAbandoned($from, $to)
    {
        $collection = $this->_logCollection->create();
        $total = 0;
        $collection->addFieldToFilter('main_table.created_at', ['gteq' => $from])
            ->addFieldToFilter('main_table.created_at', ['lteq' => $to])
            ->addFieldToSelect('quote_id');
        $collection->load();
        $collection->getSelect()->distinct()
            ->joinLeft(
                ['abacar_rule' => $collection->getTable('magenest_abacar_rule')],
                'abacar_rule.id = main_table.rule_id'
            )->group('main_table.id')->where('abacar_rule.id', AbandonedRule::RULE_ACTIVE);

        if ($collection->count()) {
            $arrayQuoteId = $collection->getColumnValues('quote_id');
            $itemsQty = $this->_quoteCollection->create()->addFieldToFilter('entity_id',['in'=>$arrayQuoteId]);
            return array_sum($itemsQty->getColumnValues('items_qty'));
        }
        return $total;
    }

    /**
     * @param $from
     * @param $to
     * @return int|mixed
     */
    public function getTotalRecoveredProduct($from, $to)
    {
        $abandonedCollection = $this->_abandonedCollection->create();
        $abandonedCollection->addFieldToFilter('main_table.created_at', ['gteq' => $from])
            ->addFieldToFilter('main_table.created_at', ['lteq' => $to])
            ->addFieldToFilter('main_table.status', AbandonedModel::STATUS_RECOVERED);
        $abandonedCollection->load();
        $abandonedCollection->getSelect()->distinct()
            ->joinLeft(
                ['abacar_rule' => $abandonedCollection->getTable('magenest_abacar_rule')],
                'abacar_rule.id = main_table.rule_id'
            )->where('abacar_rule.id', AbandonedRule::RULE_ACTIVE);
        $arrayQuoteId = $abandonedCollection->getColumnValues('quote_id');
        $itemsQty = $this->_quoteCollection->create()->addFieldToFilter('entity_id',['in'=>$arrayQuoteId]);
        return array_sum($itemsQty->getColumnValues('items_qty'));
    }

    /**
     * @param $from
     * @param $to
     * @return int
     */
    public function getRepurchasedAbandonedCarts($from, $to)
    {
        $repurchasedAbandonedCarts = $this->_abandonedCollection->create()
            ->addFieldToFilter('main_table.created_at', ['gteq' => $from])
            ->addFieldToFilter('main_table.created_at', ['lteq' => $to])
            ->addFieldToFilter('main_table.status', ["gt"=>AbandonedModel::STATUS_ABANDONED]);
        $repurchasedAbandonedCarts->load();
        $repurchasedAbandonedCarts->getSelect()->distinct()->joinLeft(
            ['abacar_rule' => $repurchasedAbandonedCarts->getTable('magenest_abacar_rule')],
            'abacar_rule.id = main_table.rule_id'
        )->where('abacar_rule.id', AbandonedRule::RULE_ACTIVE);
        return $repurchasedAbandonedCarts->count();
    }

    /**
     * @param $from
     * @param $to
     * @return int
     */
    public function getNonRepurchasedAbandonedCarts($from, $to)
    {
        $repurchasedAbandonedCarts = $this->_abandonedCollection->create()
            ->addFieldToFilter('main_table.created_at', ['gteq' => $from])
            ->addFieldToFilter('main_table.created_at', ['lteq' => $to])
            ->addFieldToFilter('main_table.status', ["eq"=>AbandonedModel::STATUS_ABANDONED]);
        $repurchasedAbandonedCarts->load();
        $repurchasedAbandonedCarts->getSelect()->distinct()->joinLeft(
            ['abacar_rule' => $repurchasedAbandonedCarts->getTable('magenest_abacar_rule')],
            'abacar_rule.id = main_table.rule_id'
        )->where('abacar_rule.id', AbandonedRule::RULE_ACTIVE);
        return $repurchasedAbandonedCarts->count();
    }
}
