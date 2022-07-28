<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Cron;

use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Plumrocket\PrivateSale\Helper\Config;
use Plumrocket\PrivateSale\Model\CleanCache;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Indexer;
use Plumrocket\PrivateSale\Model\Indexer\Product;
use Plumrocket\PrivateSale\Model\PriceCalculation;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory;

class RefreshProductPrice
{
    /**
     * @var CollectionFactory
     */
    protected $eventCollectionFactory;

    /**
     * @var ConfigResource
     */
    protected $configResource;

    /**
     * @var ReinitableConfigInterface
     */
    protected $appConfig;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var PriceCalculation
     */
    protected $priceCalculation;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var CleanCache
     */
    private $cleanCache;

    /**
     * RefreshProductPrice constructor.
     * @param DateTime $dateTime
     * @param Config $configHelper
     * @param CollectionFactory $eventCollectionFactory
     * @param ReinitableConfigInterface $appConfig
     * @param ConfigResource $configResource
     * @param IndexerRegistry $indexerRegistry
     * @param PriceCalculation $priceCalculation
     * @param CleanCache $cleanCache
     */
    public function __construct(
        DateTime $dateTime,
        Config $configHelper,
        CollectionFactory $eventCollectionFactory,
        ReinitableConfigInterface $appConfig,
        ConfigResource $configResource,
        IndexerRegistry $indexerRegistry,
        PriceCalculation $priceCalculation,
        CleanCache $cleanCache
    ) {
        $this->dateTime = $dateTime;
        $this->configHelper = $configHelper;
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->configResource = $configResource;
        $this->appConfig = $appConfig;
        $this->indexerRegistry = $indexerRegistry;
        $this->priceCalculation = $priceCalculation;
        $this->cleanCache = $cleanCache;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->configHelper->isModuleEnabled()) {

            /** @var \Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection $collection */
            $collection = $this->eventCollectionFactory->create();
            $lastFlush = $this->configHelper->getConfig(Config::LAST_FLUSH_DATE_PATH);
            $currentDateTime = $this->dateTime->gmtDate();

            if (! $lastFlush) {
                $yesterdayTimestamp = strtotime('-1 day', $this->dateTime->gmtTimestamp());
                $lastFlush = $this->dateTime->date(null, $yesterdayTimestamp);
            }

            $sqlExpression = sprintf(
                '(`event_from` >= "%s" AND `event_from` <= "%s") OR ("%s" >= `event_to` AND "%s" <= `event_to`)',
                $lastFlush,
                $currentDateTime,
                $currentDateTime,
                $lastFlush
            );

            $collection->getSelect()->having(new \Zend_Db_Expr($sqlExpression));
            $collection->addAttributeToSelect(['event_from', 'event_to'], 'left');

            if ($collection->count()) {
                $entityToEventIndexer = $this->indexerRegistry->get(Indexer::INDEX_NAME);
                $entityToEventIndexer->reindexAll();

                $eventsToProductsIndexer = $this->indexerRegistry->get(Product::INDEXER_ID);
                $eventsToProductsIndexer->reindexAll();

                $this->priceCalculation->recalculationPrice();
                $this->cleanCache->cleanCache();
            }

            $this->configResource->saveConfig(
                Config::CONFIG_SECTION . Config::LAST_FLUSH_DATE_PATH,
                $currentDateTime
            );

            $this->appConfig->reinit();
        }
    }
}
