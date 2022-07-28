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

namespace Plumrocket\PrivateSale\Model\Indexer;

use Magento\Framework\Indexer\CacheContext;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\PrivateSale\Model\Catalog\Product\GetCategoryIds;
use Plumrocket\PrivateSale\Model\Catalog\Product\GetUsedProductIdsById;
use Plumrocket\PrivateSale\Model\Catalog\Product\ProductToCategoryMapping;
use Plumrocket\PrivateSale\Model\Config\Source\EventStatus;
use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Plumrocket\PrivateSale\Model\Event;
use Plumrocket\PrivateSale\Model\Event\GetEventProducts;
use Plumrocket\PrivateSale\Model\Indexer\Builder as IndexBuilder;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexDataSorter;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRow;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRowFactory;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRowsFilter;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Reader as EntityToEventIndex;
use Plumrocket\PrivateSale\Model\Store\FrontendWebsites;

/**
 * @since 5.0.0
 */
class Product implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    const INDEXER_ID = 'pr_private_sale_product_event';

    const INDEX_NAME = 'plumrocket_privatesale_product_event';
    const UPCOMING_INDEX_NAME = 'plumrocket_privatesale_product_upcoming_event';
    const ENDED_INDEX_NAME = 'plumrocket_privatesale_product_ended_event';

    /**
     * @var EntityToEventIndex
     */
    private $entityToEventIndex;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var IndexBuilder
     */
    private $indexBuilder;

    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\Product\GetCategoryIds
     */
    private $getProductsCategoriesIds;

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRowsFilter
     */
    private $indexRowsFilter;

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexDataSorter
     */
    private $indexDataSorter;

    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\Product\ProductToCategoryMapping
     */
    private $productToCategoryMapping;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    private $cacheContext;

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRowFactory
     */
    private $entityToEventIndexRowFactory;

    /**
     * @var \Plumrocket\PrivateSale\Model\Store\FrontendWebsites
     */
    private $frontendWebsites;

    /**
     * @var \Plumrocket\PrivateSale\Model\Event\GetEventProducts
     */
    private $getEventProducts;

    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\Product\GetUsedProductIdsById
     */
    private $getUsedProductIdsById;

    /**
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Reader             $entityToEventIndex
     * @param \Magento\Store\Model\StoreManagerInterface                             $storeManager
     * @param \Plumrocket\PrivateSale\Model\Indexer\Builder                          $indexBuilder
     * @param \Plumrocket\PrivateSale\Model\Catalog\Product\GetCategoryIds           $getProductsCategoriesIds
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRowsFilter    $indexRowsFilter
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexDataSorter    $indexDataSorter
     * @param \Plumrocket\PrivateSale\Model\Catalog\Product\ProductToCategoryMapping $productToCategoryMapping
     * @param \Magento\Framework\Indexer\CacheContext                                $cacheContext
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRowFactory    $entityToEventIndexRowFactory
     * @param \Plumrocket\PrivateSale\Model\Store\FrontendWebsites                   $frontendWebsites
     * @param \Plumrocket\PrivateSale\Model\Event\GetEventProducts                   $getEventProducts
     * @param \Plumrocket\PrivateSale\Model\Catalog\Product\GetUsedProductIdsById    $getUsedProductIdsById
     */
    public function __construct(
        EntityToEventIndex $entityToEventIndex,
        StoreManagerInterface $storeManager,
        IndexBuilder $indexBuilder,
        GetCategoryIds $getProductsCategoriesIds,
        IndexRowsFilter $indexRowsFilter,
        IndexDataSorter $indexDataSorter,
        ProductToCategoryMapping $productToCategoryMapping,
        CacheContext $cacheContext,
        IndexRowFactory $entityToEventIndexRowFactory,
        FrontendWebsites $frontendWebsites,
        GetEventProducts $getEventProducts,
        GetUsedProductIdsById $getUsedProductIdsById
    ) {
        $this->entityToEventIndex = $entityToEventIndex;
        $this->storeManager = $storeManager;
        $this->indexBuilder = $indexBuilder;
        $this->getProductsCategoriesIds = $getProductsCategoriesIds;
        $this->indexRowsFilter = $indexRowsFilter;
        $this->indexDataSorter = $indexDataSorter;
        $this->productToCategoryMapping = $productToCategoryMapping;
        $this->cacheContext = $cacheContext;
        $this->entityToEventIndexRowFactory = $entityToEventIndexRowFactory;
        $this->frontendWebsites = $frontendWebsites;
        $this->getEventProducts = $getEventProducts;
        $this->getUsedProductIdsById = $getUsedProductIdsById;
    }

    /**
     * @inheritDoc
     */
    public function execute($ids)
    {
        $this->executeList($ids);
    }

    /**
     * @inheritDoc
     */
    public function executeFull()
    {
        $this->indexBuilder->clearAll();

        foreach ($this->frontendWebsites->getList() as $website) {
            $productIds = $this->getEventProducts->executeAll($website);
            $this->executeListByWebsite($productIds, $website, true);
        }

        $this->cacheContext->registerTags([Event::CACHE_TAG, \Magento\Catalog\Model\Product::CACHE_TAG]);
    }

    /**
     * @inheritDoc
     */
    public function executeList(array $ids)
    {
        foreach ($this->frontendWebsites->getList() as $website) {
            $this->executeListByWebsite($ids, $website);
        }

        $this->cacheContext->registerTags([Event::CACHE_TAG, \Magento\Catalog\Model\Product::CACHE_TAG]);
    }

    /**
     * @param array                                    $productIds
     * @param \Magento\Store\Api\Data\WebsiteInterface $website
     * @param bool                                     $skipClear
     */
    public function executeListByWebsite(array $productIds, WebsiteInterface $website, bool $skipClear = false)
    {
        $websiteId = (int) $website->getId();
        $previousStoreId = (int) $this->storeManager->getStore()->getId();
        $this->storeManager->setCurrentStore((int) $website->getDefaultGroup()->getDefaultStoreId());

        $eventsIndexRows = $this->getEventsIndexData($productIds, $websiteId);
        foreach ([EventStatus::UPCOMING, EventStatus::ACTIVE, EventStatus::ENDED] as $status) {
            $events = $this->selectAndSortEvents($eventsIndexRows, $status);
            $indexData = $this->createProductIndexData($productIds, $events, $websiteId);
            $this->indexBuilder->build($productIds, new \ArrayIterator($indexData), $status, true);
        }

        $this->storeManager->setCurrentStore($previousStoreId);
    }

    /**
     * @inheritDoc
     */
    public function executeRow($id)
    {
        $this->executeList([$id]);
    }

    /**
     * Retrieve all events for products and their categories
     *
     * @param array $productIds
     * @param int   $websiteId
     * @return \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRow[]
     */
    private function getEventsIndexData(array $productIds, int $websiteId): array
    {
        $categoriesIds = $this->getProductsCategoriesIds->execute($productIds, $websiteId);

        $productEvents = $this->entityToEventIndex->readByEntities(EventType::PRODUCT, $productIds);
        $categoryEvents = $this->entityToEventIndex->readByEntities(EventType::CATEGORY, $categoriesIds);

        $eventRows = [];
        foreach (array_merge($productEvents, $categoryEvents) as $eventIndexRowData) {
            $eventRows[] = $this->entityToEventIndexRowFactory->create(['rowData' => $eventIndexRowData]);
        }
        return $eventRows;
    }

    /**
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRow[] $eventsIndexRows
     * @param int                                                            $status
     * @return array
     */
    private function selectAndSortEvents(array $eventsIndexRows, int $status): array
    {
        $eventsByStatus = $this->indexRowsFilter->filterBySimpleStatus($eventsIndexRows, $status);
        return $this->indexDataSorter->sortByPriority($eventsByStatus);
    }

    /**
     * @param array                                                          $productIds
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRow[] $eventsIndexRows
     * @param int                                                            $websiteId
     * @return array
     */
    private function createProductIndexData(array $productIds, array $eventsIndexRows, int $websiteId): array
    {
        if (! $eventsIndexRows) {
            return [];
        }

        $result = [];
        foreach ($productIds as $productId) {
            $productId = (int) $productId;

            foreach ($eventsIndexRows as $eventsIndexRow) {
                if ($eventsIndexRow->isProductEvent()) {
                    $usedProductIds = $this->getUsedProductIdsById->execute(
                        $eventsIndexRow->getCatalogEntityId(),
                        null,
                        $websiteId
                    );
                    if (in_array($productId, $usedProductIds, false)) {
                        $result[] = $this->createIndexRowData($productId, $eventsIndexRow, $websiteId);
                        break; // leave loop and search event for next product
                    }

                    continue;
                }

                $productCategories = $this->productToCategoryMapping->getForProduct($productId);
                if (in_array($eventsIndexRow->getCatalogEntityId(), $productCategories, true)) {
                    $result[] = $this->createIndexRowData($productId, $eventsIndexRow, $websiteId);
                    break; // leave loop and search event for next product
                }
            }
        }

        return $result;
    }

    /**
     * @param int                                                          $productId
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRow $eventIndexRow
     * @param int                                                          $websiteId
     * @return array
     */
    private function createIndexRowData(int $productId, IndexRow $eventIndexRow, int $websiteId): array
    {
        return [
            IndexStructure::PRODUCT_ID => $productId,
            IndexStructure::WEBSITE_ID => $websiteId,
            IndexStructure::EVENT_ID => $eventIndexRow->getEventId(),
            IndexStructure::IS_PRIVATE => (int) $eventIndexRow->isPrivate(),
            IndexStructure::EVENT_FROM => $eventIndexRow->getEventActiveFrom(),
            IndexStructure::EVENT_TO => $eventIndexRow->getEventActiveTo(),
        ];
    }
}
