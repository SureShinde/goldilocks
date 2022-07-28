<?php

namespace Magenest\AbandonedCart\Block\Adminhtml\Chart\Edit\Tab;

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
use Magenest\AbandonedCart\Model\ResourceModel\ABTestCampaign\CollectionFactory as CampaignCollection;

/**
 * Class GridRule
 * @package Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaigns\Edit\Tab
 */
class CampaignGrid extends \Magento\Backend\Block\Widget\Grid\Extended
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

    /** @var CampaignCollection $_campaignCollection */
    protected $_campaignCollection;

    /**
     * GridRule constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param ProductFactory $productFactory
     * @param Registry $coreRegistry
     * @param ABTestCampaignFactory $ABTestCampaignModel
     * @param ABTestCampaignResource $ABTestCampaignResource
     * @param CampaignCollection $campaignCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ProductFactory $productFactory,
        Registry $coreRegistry,
        ABTestCampaignFactory $ABTestCampaignModel,
        ABTestCampaignResource $ABTestCampaignResource,
        CampaignCollection $campaignCollection,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_aBTestCampaignModel = $ABTestCampaignModel->create();
        $this->_aBTestCampaignResource = $ABTestCampaignResource;
        $this->_campaignCollection = $campaignCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setId('grid_campaign_report');
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
        $dataFilter = $this->getRequest()->getParams();
        $collection = $this->_campaignCollection->create();
        if (isset($dataFilter['from']) || isset($dataFilter['to'])) {
            $fromDate = date('yy-m-d', strtotime($dataFilter['from']));
            $toDate = date('yy-m-d', strtotime($dataFilter['to']));
            $collection->addFieldToFilter('main_table.status', 1)
                ->addFieldToFilter('from_date', ['gteq' => $fromDate])
                ->addFieldToFilter('to_date', ['lteq' => $toDate]);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        )->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => [0 => __('Inactive'),1 => __('Active')],
                'renderer' => \Magenest\AbandonedCart\Block\Adminhtml\Chart\Edit\Tab\Renderer\Status::class,
            ]
        )->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'renderer' => \Magenest\AbandonedCart\Block\Adminhtml\Chart\Edit\Tab\Renderer\NameCampaign::class,
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
        )->addColumn(
            'action',
            [
                'header' => __('Action'),
                'width' => '100',
                'type' => 'action',
                'renderer' => \Magenest\AbandonedCart\Block\Adminhtml\Chart\Edit\Tab\Renderer\Action::class,
                'filter' => false,
                'sortable' => false,
                'is_system' => true
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
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $item
     * @return bool|string
     */
    public function getRowUrl($item)
    {
        return false;
    }
}
