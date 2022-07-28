<?php

namespace Magenest\AbandonedCart\Controller\Adminhtml;

use Magenest\AbandonedCart\Helper\Data;
use Magenest\AbandonedCart\Helper\MandrillConnector;
use Magenest\AbandonedCart\Helper\SendMail;
use Magenest\AbandonedCart\Model\ABTestCampaignFactory;
use Magenest\AbandonedCart\Model\Cron;
use Magenest\AbandonedCart\Model\LogContentFactory;
use Magenest\AbandonedCart\Model\ResourceModel\ABTestCampaign\CollectionFactory as CampaignCollectionFactory;
use Magenest\AbandonedCart\Model\ResourceModel\ABTestCampaign as ABTestCampaignResource;
use Magenest\AbandonedCart\Model\ResourceModel\Rule\Collection;
use Magenest\AbandonedCart\Model\TestCampaignFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Registry as RegistryAlias;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;
use Magento\Backend\App\Action\Context;

abstract class ABTestCampaigns extends \Magento\Backend\App\Action
{

    /** @var ABTestCampaignFactory $_aBTestCampaignFactory */
    protected $_aBTestCampaignFactory;

    /** @var  CampaignCollectionFactory $_campaignCollectionFactory */
    protected $_campaignCollectionFactory;

    /** @var ABTestCampaignResource $_abTestCampaignResource */
    protected $_abTestCampaignResource;

    /** @var  Cron $_cronJob */
    protected $_cronJob;

    /** @var  TestCampaignFactory $_testCampaignFactory */
    protected $_testCampaignFactory;

    /** @var DateTime $_dateTime */
    protected $_dateTime;

    /** @var Data $_helperData */
    protected $_helperData;

    /** @var  LogContentFactory $_logContent */
    protected $_logContentFactory;

    /** @var  SendMail $_sendMailHelper */
    protected $_sendMailHelper;

    /** @var MandrillConnector $_mandrillConnector */
    protected $_mandrillConnector;

    /** @var  RawFactory $rawResultFactory */
    protected $resultRawFactory;

    /** @var  Filter $_filer */
    protected $_filer;

    /** @var  LoggerInterface $_logger */
    protected $_logger;

    /** @var  RegistryAlias $_coreRegistry */
    protected $_coreRegistry;

    /** @var PageFactory $_resultPageFactory */
    protected $_resultPageFactory;

    /** @var TimezoneInterface $_localeDate */
    protected $_localeDate;

    /** @var LayoutFactory $resultLayoutFactory */
    protected $resultLayoutFactory;

    /** @var JsonFactory $resultJsonFactory */
    protected $resultJsonFactory;

    /** @var Collection $_ruleCollectionFactory */
    protected $_ruleCollectionFactory;

    /**
     * ABTestCampaigns constructor.
     * @param ABTestCampaignFactory $aBTestCampaignFactory
     * @param CampaignCollectionFactory $campaignCollectionFactory
     * @param ABTestCampaignResource $abTestCampaignResourceFactory
     * @param Collection $ruleCollectionFactory
     * @param Cron $cron
     * @param TestCampaignFactory $testCampaignFactory
     * @param DateTime $dateTime
     * @param Data $helperData
     * @param LogContentFactory $contentFactory
     * @param SendMail $sendMail
     * @param MandrillConnector $mandrillConnector
     * @param RawFactory $rawResultFactory
     * @param Filter $filter
     * @param LoggerInterface $logger
     * @param RegistryAlias $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param TimezoneInterface $timezone
     * @param LayoutFactory $resultLayoutFactory
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     */

    public function __construct(
        ABTestCampaignFactory $aBTestCampaignFactory,
        CampaignCollectionFactory $campaignCollectionFactory,
        ABTestCampaignResource $abTestCampaignResourceFactory,
        Collection $ruleCollectionFactory,
        Cron $cron,
        TestCampaignFactory $testCampaignFactory,
        DateTime $dateTime,
        Data $helperData,
        LogContentFactory $contentFactory,
        SendMail $sendMail,
        MandrillConnector $mandrillConnector,
        RawFactory $rawResultFactory,
        Filter $filter,
        LoggerInterface $logger,
        RegistryAlias $coreRegistry,
        PageFactory $resultPageFactory,
        TimezoneInterface $timezone,
        LayoutFactory $resultLayoutFactory,
        JsonFactory $resultJsonFactory,
        Context $context
    ) {
        $this->_aBTestCampaignFactory = $aBTestCampaignFactory;
        $this->_campaignCollectionFactory = $campaignCollectionFactory;
        $this->_abTestCampaignResource = $abTestCampaignResourceFactory;
        $this->_ruleCollectionFactory = $ruleCollectionFactory;
        $this->_cronJob = $cron;
        $this->_testCampaignFactory = $testCampaignFactory;
        $this->_dateTime = $dateTime;
        $this->_helperData = $helperData;
        $this->_logContentFactory = $contentFactory;
        $this->_sendMailHelper = $sendMail;
        $this->_mandrillConnector = $mandrillConnector;
        $this->resultRawFactory = $rawResultFactory;
        $this->_filer = $filter;
        $this->_logger = $logger;
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_localeDate = $timezone;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_AbandonedCart::abtestcampaign');
    }
}
