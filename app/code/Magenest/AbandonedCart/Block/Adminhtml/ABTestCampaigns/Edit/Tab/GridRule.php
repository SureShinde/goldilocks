<?php

namespace Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaigns\Edit\Tab;

use Magenest\AbandonedCart\Model\ABTestCampaign;
use Magenest\AbandonedCart\Model\Config\Source\Mail;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Registry;
use Magenest\AbandonedCart\Model\ABTestCampaignFactory;
use Magenest\AbandonedCart\Model\ResourceModel\ABTestCampaignFactory as ABTestCampaignResource;
use Magenest\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Magenest\AbandonedCart\Helper\Data as AbandonedHelper;

/**
 * Class GridRule
 * @package Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaigns\Edit\Tab
 */
class GridRule extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     * @var Registry $_coreRegistry
     */
    protected $_coreRegistry = null;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

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

    /** @var AbandonedHelper $_abandonedHelper */
    protected $_abandonedHelper;

    /**
     * GridRule constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param ProductFactory $productFactory
     * @param Registry $coreRegistry
     * @param ABTestCampaignFactory $ABTestCampaignModel
     * @param ABTestCampaignResource $ABTestCampaignResource
     * @param RuleCollection $ruleCollection
     * @param AbandonedHelper $abandonedHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ProductFactory $productFactory,
        Registry $coreRegistry,
        ABTestCampaignFactory $ABTestCampaignModel,
        ABTestCampaignResource $ABTestCampaignResource,
        RuleCollection $ruleCollection,
        AbandonedHelper $abandonedHelper,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_aBTestCampaignModel = $ABTestCampaignModel->create();
        $this->_aBTestCampaignResource = $ABTestCampaignResource->create();
        $this->_ruleCollection = $ruleCollection;
        $this->_abandonedHelper = $abandonedHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setId('collection_rule_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setDefaultLimit(50);
        parent::_construct();
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $dataCampaign = $this->getABTestCampaignModel()->getData();
        if (!empty($dataCampaign)) {

            /** format date before filter */
            $fromDate = date('y-m-d', strtotime($dataCampaign['from_date']));
            $toDate = date('y-m-d', strtotime($dataCampaign['to_date']));

            /** @var RuleCollection $collection */
            /** collection data for show in grid */
            $collection = $this->_ruleCollection->create()
                ->addFieldToFilter('main_table.status', 1)
                ->addFieldToFilter('from_date', ['gteq' => $fromDate])
                ->addFieldToFilter('to_date', ['lteq' => $toDate])
                ->addFilterToMap('id', 'main_table.id');

            $this->setCollection($this->_abandonedHelper->getCollectionRule($collection));
        }

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'select_rule',
            [
                'type' => 'checkbox',
                'name' => 'select_rule',
                'index' => 'id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        )->addColumn(
            'id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        )->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'renderer' => \Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaigns\Edit\Tab\Renderer\NameRule::class,
            ]
        )->addColumn(
            'emails',
            [
                'type' => 'number',
                'header' => __('Message Generated'),
                'index' => 'emails'
            ]
        )->addColumn(
            'sent',
            [
                'type' => 'number',
                'header' => __('Successfully Sent'),
                'index' => 'sent'
            ]
        )->addColumn(
            'opened',
            [
                'type' => 'number',
                'header' => __('Opens'),
                'index' => 'opened'
            ]
        )->addColumn(
            'clicks',
            [
                'type' => 'number',
                'header' => __('Clicks'),
                'index' => 'clicks',
                'renderer' => \Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaigns\Edit\Tab\Renderer\Clicks::class,
            ]
        )->addColumn(
            'restore',
            [
                'type' => 'number',
                'header' => __('Carts Restored'),
                'index' => 'restore'
            ]
        )->addColumn(
            'from_date',
            [
                'type' => 'date',
                'header' => __('From Date'),
                'index' => 'from_date',
                'timezone' => false,
                'column_css_class' => 'col-date',
                'header_css_class' => 'col-date',
            ]
        )->addColumn(
            'to_date',
            [
                'type' => 'date',
                'header' => __('To Date'),
                'index' => 'to_date',
                'timezone' => false,
                'column_css_class' => 'col-date',
                'header_css_class' => 'col-date',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * @return ABTestCampaign|ABTestCampaignFactory
     */
    private function getABTestCampaignModel()
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
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getABTestCampaignModel()->getId() || $this->getABTestCampaignModel()->getStatus()) {
            return parent::_toHtml();
        }
        $html = '<div><span>%s</span></div>';
        $html = sprintf($html, __("Please enable and save test campaign to collection campaign."));

        return $html;
    }

    public function getRowUrl($item)
    {
        return false;
    }
}
