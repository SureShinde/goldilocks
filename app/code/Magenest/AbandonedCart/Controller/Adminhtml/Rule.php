<?php

namespace Magenest\AbandonedCart\Controller\Adminhtml;

use Magenest\AbandonedCart\Helper\Data;
use Magenest\AbandonedCart\Helper\MandrillConnector;
use Magenest\AbandonedCart\Helper\SendMail;
use Magenest\AbandonedCart\Model\Cron;
use Magenest\AbandonedCart\Model\LogContentFactory;
use Magenest\AbandonedCart\Model\ResourceModel\LogContent\CollectionFactory as LogContentCollection;
use Magenest\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory;
use Magenest\AbandonedCart\Model\ResourceModel\Rule as ResourceRule;
use Magenest\AbandonedCart\Model\RuleFactory;
use Magenest\AbandonedCart\Model\TestCampaignFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Serialize\Serializer\Json;

abstract class Rule extends Action
{

    /** @var RuleFactory $_ruleFactory */
    protected $_ruleFactory;

    /** @var  ResourceRule $_resourceRule */
    protected $_resourceRule;

    /** @var  CollectionFactory $_collectionFactory */
    protected $_collectionFactory;

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

    /** @var LogContentCollection  */
    protected $_logContentCollection;

    /** @var  SendMail $_sendMailHelper */
    protected $_sendMailHelper;

    /** @var MandrillConnector $_mandrillConnector */
    protected $_mandrillConnector;

    /** @var  RawFactory $rawResultFactory */
    protected $resultRawFactory;

    /** @var  Filter $_filer */
    protected $_filer;

    /** @var  \Psr\Log\LoggerInterface $_logger */
    protected $_logger;

    /** @var  Registry $_coreRegistry */
    protected $_coreRegistry;

    /** @var PageFactory $_resultPageFactory */
    protected $_resultPageFactory;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /** @var Json */
    protected $_json;

    /**
     * Rule constructor.
     * @param RuleFactory $ruleFactory
     * @param ResourceRule $resourceRule
     * @param CollectionFactory $collectionFactory
     * @param Cron $cron
     * @param TestCampaignFactory $testCampaignFactory
     * @param DateTime $dateTime
     * @param Data $helperData
     * @param LogContentFactory $contentFactory
     * @param SendMail $sendMail
     * @param MandrillConnector $mandrillConnector
     * @param RawFactory $rawResultFactory
     * @param Filter $filter
     * @param \Psr\Log\LoggerInterface $logger
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param TimezoneInterface $timezone
     * @param LayoutFactory $resultLayoutFactory
     * @param JsonFactory $resultJsonFactory
     * @param LogContentCollection $logContentCollection
     * @param Context $context
     * @param Json $json
     */
    public function __construct(
        RuleFactory $ruleFactory,
        ResourceRule $resourceRule,
        CollectionFactory $collectionFactory,
        Cron $cron,
        TestCampaignFactory $testCampaignFactory,
        DateTime $dateTime,
        Data $helperData,
        LogContentFactory $contentFactory,
        SendMail $sendMail,
        MandrillConnector $mandrillConnector,
        RawFactory $rawResultFactory,
        Filter $filter,
        \Psr\Log\LoggerInterface $logger,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        TimezoneInterface $timezone,
        LayoutFactory $resultLayoutFactory,
        JsonFactory $resultJsonFactory,
        LogContentCollection $logContentCollection,
        Context $context,
        Json $json
    ) {
        $this->_ruleFactory         = $ruleFactory;
        $this->_resourceRule     = $resourceRule;
        $this->_collectionFactory   = $collectionFactory;
        $this->_cronJob             = $cron;
        $this->_testCampaignFactory = $testCampaignFactory;
        $this->_dateTime            = $dateTime;
        $this->_helperData          = $helperData;
        $this->_logContentFactory   = $contentFactory;
        $this->_sendMailHelper      = $sendMail;
        $this->_mandrillConnector   = $mandrillConnector;
        $this->resultRawFactory     = $rawResultFactory;
        $this->_filer               = $filter;
        $this->_logger              = $logger;
        $this->_coreRegistry        = $coreRegistry;
        $this->_resultPageFactory   = $resultPageFactory;
        $this->_localeDate          = $timezone;
        $this->resultLayoutFactory  = $resultLayoutFactory;
        $this->resultJsonFactory    = $resultJsonFactory;
        $this->_logContentCollection = $logContentCollection;
        $this->_json = $json;
        parent::__construct($context);
    }

    public function geNotiLogId($ruleId)
    {
        $flag = false;
        try {
            $collection = $this->_logContentFactory->create()
                ->addFieldToFilter('rule_id', $ruleId)
                ->getFirstItem();
            if ($collection->getRuleId()) {
                $flag = true;
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
        return $flag;
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_AbandonedCart::rule');
    }
}
