<?php

namespace Magenest\AbandonedCart\Helper;

use Magenest\AbandonedCart\Model\AbandonedCart;
use Magenest\AbandonedCart\Model\Config\Source\Mail;
use Magenest\AbandonedCart\Model\LogContentFactory;
use Magenest\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ABANDONED_CART_PERIOD = "abandonedcart/setting/considered_member";

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;

    /** @var StoreManagerInterface $storeManager */
    protected $storeManager;

    /** @var Session $_customerSession */
    protected $_customerSession;

    /** @var RuleCollection $_ruleCollection */
    protected $_ruleCollection;

    /** @var LogContentFactory $_logContentFactory */
    protected $_logContentFactory;

    /**
     * Data constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param Context $context
     * @param RuleCollection $ruleCollection
     * @param LogContentFactory $logContentFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Session $customerSession,
        Context $context,
        RuleCollection $ruleCollection,
        LogContentFactory $logContentFactory
    ) {
        $this->storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_ruleCollection = $ruleCollection;
        $this->_logContentFactory = $logContentFactory;
        parent::__construct($context);
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getId();
    }

    /**
     * @return mixed|string
     */
    public function getAbandonedCartPeriod()
    {
        $timePeriod = $this->scopeConfig->getValue(self::ABANDONED_CART_PERIOD);
        if ($timePeriod == '' || $timePeriod == null) {
            $timePeriod = '60';
        }
        return $timePeriod;
    }

    /**
     * @param $minutes
     * @return string
     * @throws \Exception
     */
    public function formateDate($minutes)
    {
        $modify = '+' . $minutes . ' minutes';
        $now = new \DateTime();
        $now->modify($modify);
        $date = $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        return $date;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        $value = $this->scopeConfig->getValue($path);
        return $value;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return string
     */
    public function getVersionMagento()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        try {
            $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
            $version = $productMetadata->getVersion();
        } catch (\Exception $e) {
            $version = '0.0.0';
            $this->_logger->critical($e->getMessage());
        }
        return $version;
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function getCollectionRule($collection)
    {
        $collection->getSelect()
            ->joinLeft(
                ['log_emails' => $this->getEmailCountSelect()],
                'log_emails.id = main_table.id',
                'log_emails.emails'
            )->joinLeft(
                ['log_sent' => $this->getEmailSentSelect()],
                'log_sent.id = main_table.id',
                'log_sent.sent'
            )->joinLeft(
                ['log_opened' => $this->getEmailOpenedSelect()],
                'log_opened.id = main_table.id',
                'log_opened.opened'
            )->joinLeft(
                ['log_clicks' => $this->getClickCountSelect()],
                'log_clicks.id = main_table.id',
                'log_clicks.clicks'
            )->joinLeft(
                ['log_restore' => $this->countCartRestore()],
                'log_restore.id = main_table.id',
                'log_restore.restore'
            );
        return $collection;
    }

    /**
     * @return Select
     */
    private function getEmailCountSelect()
    {
        $logEmails = $this->_ruleCollection->create();
        $logEmails->getSelect()
            ->reset(Select::COLUMNS)
            ->joinLeft(
                ['abacar_log' => $logEmails->getTable('magenest_abacar_log')],
                'abacar_log.rule_id = main_table.id',
                'COUNT(abacar_log.id) as emails'
            )->columns('main_table.id')
            ->group('main_table.id');
        return $logEmails->getSelect();
    }

    /**
     * @return Select
     */
    private function getEmailSentSelect()
    {
        $logSent = $this->_ruleCollection->create();
        $logSent->getSelect()
            ->reset(Select::COLUMNS)
            ->joinLeft(
                ['abacar_log' => $logSent->getTable('magenest_abacar_log')],
                'abacar_log.rule_id = main_table.id AND abacar_log.status=' . Mail::STATUS_SENT,
                'COUNT(abacar_log.id) as sent'
            )->columns('main_table.id')
            ->group('main_table.id');
        return $logSent->getSelect();
    }

    /**
     * @return Select
     */
    private function getEmailOpenedSelect()
    {
        $logOpened = $this->_ruleCollection->create();
        $logOpened->getSelect()
            ->reset(Select::COLUMNS)
            ->joinLeft(
                ['log_opened' => $logOpened->getTable('magenest_abacar_log')],
                'log_opened.rule_id = main_table.id AND log_opened.opened > 0',
                'COUNT(log_opened.id) as opened'
            )->columns('main_table.id')
            ->group('main_table.id');
        return $logOpened->getSelect();
    }

    /**
     * @return Select
     */
    private function getClickCountSelect()
    {
        $logClicks = $this->_ruleCollection->create();
        $logClicks->getSelect()
            ->reset(Select::COLUMNS)
            ->joinLeft(
                ['log_clicks' => $logClicks->getTable('magenest_abacar_log')],
                'log_clicks.rule_id = main_table.id',
                'SUM(log_clicks.clicks) as clicks'
            )->columns('main_table.id')
            ->group('main_table.id');
        return $logClicks->getSelect();
    }


    /**
     * @return Select
     */
    private function countCartRestore()
    {
        $logEmails = $this->_ruleCollection->create();
        $logEmails->getSelect()
            ->reset(Select::COLUMNS)
            ->joinLeft(
                ['abacar_log' => $logEmails->getTable('magenest_abacar_log')],
                'abacar_log.rule_id = main_table.id AND abacar_log.is_restore like 1',
                'COUNT(abacar_log.is_restore) as restore'
            )->columns('main_table.id')
            ->group('main_table.id');
        return $logEmails->getSelect();
    }
}
