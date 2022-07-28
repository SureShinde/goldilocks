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

namespace Plumrocket\PrivateSale\Model\Indexer\EntityToEvent;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Api\EventRepositoryInterface;
use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Plumrocket\PrivateSale\Model\Event\IndexData;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection as EventCollection;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory as EventCollectionFactory;
use Plumrocket\PrivateSale\Model\Store\FrontendWebsites;
use Psr\Log\LoggerInterface;

/**
 * @since 5.0.0
 */
class Indexer implements ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    const INDEX_NAME = 'plumrocket_private_sale_catalog_event';

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Builder
     */
    private $indexBuilder;

    /**
     * @var EventCollection
     */
    private $eventCollection;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Plumrocket\PrivateSale\Api\EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory
     */
    private $eventCollectionFactory;

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexDataSorter
     */
    private $indexDataSorter;

    /**
     * @var \Plumrocket\PrivateSale\Model\Event\IndexData
     */
    private $indexData;

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRowFactory
     */
    private $indexRowFactory;

    /**
     * @var \Plumrocket\PrivateSale\Model\Store\FrontendWebsites
     */
    private $frontendWebsites;

    /**
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Builder         $indexBuilder
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection        $eventCollection
     * @param \Psr\Log\LoggerInterface                                            $logger
     * @param \Plumrocket\PrivateSale\Api\EventRepositoryInterface                $eventRepository
     * @param \Magento\Store\Model\StoreManagerInterface                          $storeManager
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory $eventCollectionFactory
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexDataSorter $indexDataSorter
     * @param \Plumrocket\PrivateSale\Model\Event\IndexData                       $indexData
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRowFactory $indexRowFactory
     * @param \Plumrocket\PrivateSale\Model\Store\FrontendWebsites                $frontendWebsites
     */
    public function __construct(
        Builder $indexBuilder,
        EventCollection $eventCollection,
        LoggerInterface $logger,
        EventRepositoryInterface $eventRepository,
        StoreManagerInterface $storeManager,
        EventCollectionFactory $eventCollectionFactory,
        IndexDataSorter $indexDataSorter,
        IndexData $indexData,
        IndexRowFactory $indexRowFactory,
        FrontendWebsites $frontendWebsites
    ) {
        $this->indexBuilder = $indexBuilder;
        $this->eventCollection = $eventCollection;
        $this->logger = $logger;
        $this->eventRepository = $eventRepository;
        $this->storeManager = $storeManager;
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->indexDataSorter = $indexDataSorter;
        $this->indexData = $indexData;
        $this->indexRowFactory = $indexRowFactory;
        $this->frontendWebsites = $frontendWebsites;
    }

    /*
     * Used by mview, allows process indexer in the "Update on schedule" mode
     */
    public function execute($ids)
    {
        //Used by mview, allows you to process multiple placed orders in the "Update on schedule" mode
    }

    /**
     * @inheritDoc
     */
    public function executeFull()
    {
        $this->executeList([]);
    }

    /**
     * @inheritDoc
     */
    public function executeList(array $ids)
    {
        if (empty($ids)) {
            $ids = $this->eventCollection->getAllIds();
            if (empty($ids)) {
                return;
            }
        }

        try {
            $eventsData = $this->aggregateEventData(array_values($ids));
            $this->indexBuilder->build($ids, $eventsData);
            $this->syncCatalogEntitiesStatus($eventsData);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function executeRow($id)
    {
        $this->executeList([$id]);
    }

    /**
     * @param array $ids
     * @return array
     */
    private function aggregateEventData(array $ids) : array
    {
        $data = [];
        if ($this->storeManager->isSingleStoreMode()) {
            $data = $this->aggregateWebsiteEventData((int) $this->storeManager->getWebsite()->getId(), $ids, $data);
        } else {
            $savedStoreId = (int) $this->storeManager->getStore()->getId();

            foreach ($this->frontendWebsites->getList() as $website) {
                $this->storeManager->setCurrentStore((int) $website->getDefaultGroup()->getDefaultStoreId());
                $data = $this->aggregateWebsiteEventData((int) $website->getId(), $ids, $data);
            }

            $this->storeManager->setCurrentStore($savedStoreId);
        }

        return $data;
    }

    /**
     * @param int   $websiteId
     * @param int[] $eventIds
     * @param array $data
     * @return \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRow[]
     */
    private function aggregateWebsiteEventData(int $websiteId, array $eventIds, array $data): array
    {
        foreach ($this->getEvents($eventIds) as $event) {
            $catalogEntityId = $event->isCategoryEvent() ? $event->getCategoryId() : $event->getProductId();

            /** @var \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRow $indexRow */
            $indexRow = $this->indexRowFactory->create(
                [
                    'rowData' => [
                        Structure::EVENT_ID   => $event->getId(),
                        Structure::WEBSITE_ID => $websiteId,
                        Structure::TYPE       => $event->getType(),
                        Structure::ENTITY_ID  => $catalogEntityId,
                        Structure::PRIORITY   => $event->getPriority(),
                        Structure::IS_PRIVATE => $event->isEventPrivate(),
                        Structure::EVENT_FROM => $event->getActiveFrom(),
                        Structure::EVENT_TO   => $event->getActiveTo(),
                    ],
                ]
            );

            $data[] = $indexRow;
        }

        return $data;
    }

    /**
     * @param int[] $eventIds
     * @return EventInterface[]
     */
    private function getEvents(array $eventIds): array
    {
        if (1 === count($eventIds)) {
            try {
                $event = $this->eventRepository->getById($eventIds[0]);

                return $event->isEnabled() ? [$event] : [];
            } catch (NoSuchEntityException $e) {
                return [];
            }
        }

        /** @var EventCollection $collection */
        $collection = $this->eventCollectionFactory->create();

        $collection->addAttributeToSelect(
            [
                EventInterface::IDENTIFIER,
                EventInterface::PRODUCT_EVENT,
                EventInterface::CATEGORY_EVENT,
                EventInterface::EVENT_TYPE,
                EventInterface::EVENT_FROM,
                EventInterface::EVENT_TO,
                EventInterface::IS_PRIVATE,
                EventInterface::PRIORITY,
            ]
        );

        $collection
            ->addAttributeToFilter('enable', 1, 'left')
            ->addFieldToFilter(EventInterface::IDENTIFIER, ['in' => $eventIds])
            ->setOrder(EventInterface::PRIORITY, 'ASC');

        return $collection->getItems();
    }

    /**
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRow[] $eventsData
     */
    private function syncCatalogEntitiesStatus(array $eventsData)
    {
        $sortedData = $this->indexDataSorter->sortByPriority($eventsData);

        $syncEntities = [];
        foreach ($sortedData as $eventIndexRow) {
            $catalogEntityId = $eventIndexRow->getCatalogEntityId();
            $catalogEntityType = $eventIndexRow->getType();
            $websiteId = $eventIndexRow->getWebsiteId();

            if (! isset($syncEntities[$websiteId])) {
                $syncEntities[$websiteId] = [
                    EventType::PRODUCT => [],
                    EventType::CATEGORY => [],
                ];
            }

            // sync only with first event because it has the highest priority
            if (isset($syncEntities[$websiteId][$catalogEntityType][$catalogEntityId])) {
                continue;
            }

            $syncEntities[$websiteId][$catalogEntityType][$catalogEntityId] = $eventIndexRow;
        }

        $this->indexData->massSync($syncEntities);
    }
}
