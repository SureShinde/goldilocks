<?php

namespace Magenest\WebApiLog\Cron;

use Magenest\WebApiLog\Model\ResourceModel\ApiLog\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ClearLog
 *
 * @package Magenest\WebApiLog\Cron
 */
class ClearLog
{
    /**
     * Default date format
     *
     * @var string
     */
    const DATE_FORMAT = 'Y-m-d H:i:s';

    const KEEP_LOG_DAY_NEAREST = 'api_log/general_settings/hold_log';

    const API_LOG_ENABLE = 'api_log/general_settings/enable';

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CollectionFactory
     */
    private $apiLogCollection;

    /**
     * ClearLog constructor.
     *
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $collectionFactory
    ) {
        $this->logger             = $logger;
        $this->dateTime           = $dateTime;
        $this->scopeConfig = $scopeConfig;
        $this->apiLogCollection = $collectionFactory;
    }

    /**
     * Return cron cleanup date
     *
     * @return null|string
     */
    public function __getDate()
    {
        $timestamp = $this->dateTime->gmtTimestamp();
        $day       = $this->scopeConfig->getValue(self::KEEP_LOG_DAY_NEAREST, ScopeInterface::SCOPE_STORE);
        if ($day) {
            $timestamp -= $day * 24 * 60 * 60;
            return $this->dateTime->gmtDate(self::DATE_FORMAT, $timestamp);
        }
        return null;
    }

    /**
     * Delete record which date is less than the current date
     *
     * @return $this|null
     */
    public function execute()
    {
        try {
            if (!$this->scopeConfig->getValue(self::API_LOG_ENABLE, ScopeInterface::SCOPE_STORE)) {
                return null;
            }
            if($date = $this->__getDate()) {
                $apiLogs = $this->getListBeforeDate($date);
                if (!empty($apiLogs)) {
                    foreach ($apiLogs as $apiLog) {
                        $apiLog->delete();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return null;
    }

    /**
     * Get all api log data before date
     *
     * @param $endDate
     * @return \Magenest\WebApiLog\Model\ResourceModel\ApiLog\Collection
     */
    public function getListBeforeDate($endDate)
    {
        return $this->apiLogCollection->create()
            ->addFieldToSelect('id')
            ->addFieldToFilter('created_at', ["lteq" => $endDate]);
    }
}
