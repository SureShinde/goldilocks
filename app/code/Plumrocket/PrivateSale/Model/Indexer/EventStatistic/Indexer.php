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
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\Indexer\EventStatistic;

use Exception;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Plumrocket\PrivateSale\Model\Event;
use Plumrocket\PrivateSale\Model\EventStatistics\Calculation;
use Psr\Log\LoggerInterface;

/**
 * @since 5.0.0
 */
class Indexer
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Plumrocket\PrivateSale\Model\EventStatistics\Calculation
     */
    private $statisticCalculation;

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\EventStatistic\Builder
     */
    private $indexBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\EventStatistic\IndexRowFactory
     */
    private $indexRowFactory;

    /**
     * @var \Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory
     */
    private $eventCollectionFactory;

    /**
     * @param \Psr\Log\LoggerInterface                                             $logger
     * @param \Plumrocket\PrivateSale\Model\EventStatistics\Calculation            $statisticCalculation
     * @param \Plumrocket\PrivateSale\Model\Indexer\EventStatistic\Builder         $indexBuilder
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory      $categoryCollectionFactory
     * @param \Plumrocket\PrivateSale\Model\Indexer\EventStatistic\IndexRowFactory $indexRowFactory
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory  $eventCollectionFactory
     */
    public function __construct(
        LoggerInterface $logger,
        Calculation $statisticCalculation,
        Builder $indexBuilder,
        CollectionFactory $categoryCollectionFactory,
        IndexRowFactory $indexRowFactory,
        \Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory $eventCollectionFactory
    ) {
        $this->logger = $logger;
        $this->statisticCalculation = $statisticCalculation;
        $this->indexBuilder = $indexBuilder;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->indexRowFactory = $indexRowFactory;
        $this->eventCollectionFactory = $eventCollectionFactory;
    }

    /**
     * Rebuild statistic for events and homepages
     */
    public function executeFull()
    {
        try {
            $this->indexBuilder->clearAll();
            $this->indexBuilder->build($this->aggregateEventData());
            $this->indexBuilder->build($this->aggregateHomepageData());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @return \Plumrocket\PrivateSale\Model\Indexer\EventStatistic\IndexRow[]
     */
    private function aggregateEventData(): array
    {
        /** @var \Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection $collection */
        $collection = $this->eventCollectionFactory->create();

        $eventStatistics = $this->statisticCalculation->calculateByEvents($collection->getItems());

        /** @var IndexRow[] $indexRows */
        $indexRows = [];
        foreach ($eventStatistics as $eventId => $statisticData) {
            $statisticData[Structure::ENTITY_ID] = $eventId;
            $indexRows[] = $this->indexRowFactory->create(['rowData' => $statisticData]);
        }

        return $indexRows;
    }

    /**
     * @return \Plumrocket\PrivateSale\Model\Indexer\EventStatistic\IndexRow[]
     */
    private function aggregateHomepageData(): array
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create();

        $items = $collection
            ->setStoreId(0)
            ->addFieldToFilter('display_mode', ['eq' => Event::DM_HOMEPAGE])
            ->toArray();

        $homepageStatistics = $this->statisticCalculation->calculateByHomepages($items);

        /** @var IndexRow[] $indexRows */
        $indexRows = [];
        foreach ($homepageStatistics as $categoryId => $statisticData) {
            $statisticData[Structure::ENTITY_ID] = $categoryId;
            $indexRows[] = $this->indexRowFactory->create(['rowData' => $statisticData]);
        }

        return $indexRows;
    }
}
