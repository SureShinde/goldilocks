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

namespace Plumrocket\PrivateSale\Model\Event;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Api\EventRepositoryInterface;
use Plumrocket\PrivateSale\Helper\Config as ConfigHelper;
use Plumrocket\PrivateSale\Model\Config\Source\EventStatus;
use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRow;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory;
use Plumrocket\PrivateSale\Model\Store\FrontendWebsites;

/**
 * @since 5.0.0
 */
class IndexData
{
    const BEFORE_EVENT_START = 'before';
    const AFTER_EVENT_END = 'after';

    /**
     * @var CollectionFactory
     */
    private $eventCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Plumrocket\PrivateSale\Model\Store\FrontendWebsites
     */
    private $frontendWebsites;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Api\CategoryListInterface
     */
    private $categoryList;

    /**
     * @var \Plumrocket\PrivateSale\Api\EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                     $productRepository
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface                    $categoryRepository
     * @param \Magento\Store\Model\StoreManagerInterface                          $storeManager
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory $eventCollectionFactory
     * @param \Plumrocket\PrivateSale\Model\Store\FrontendWebsites                $frontendWebsites
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                        $searchCriteriaBuilder
     * @param \Magento\Catalog\Api\CategoryListInterface                          $categoryList
     * @param \Plumrocket\PrivateSale\Api\EventRepositoryInterface                $eventRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        CollectionFactory $eventCollectionFactory,
        FrontendWebsites $frontendWebsites,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CategoryListInterface $categoryList,
        EventRepositoryInterface $eventRepository
    ) {
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->frontendWebsites = $frontendWebsites;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->categoryList = $categoryList;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @var void
     */
    public function syncStatusBeforeEventStarts()
    {
        $this->syncStatus(
            [$this, 'getUpcomingEventsCollection'],
            EventStatus::UPCOMING
        );
    }

    /**
     * @var void
     */
    public function syncStatusAfterEventEnds()
    {
        $this->syncStatus(
            [$this, 'getEndedEventsCollection'],
            EventStatus::ENDED
        );
    }

    /**
     * @param callable $eventCollectionProvider
     * @param int      $simpleStatus
     */
    private function syncStatus(callable $eventCollectionProvider, int $simpleStatus)
    {
        foreach ($this->frontendWebsites->getList() as $website) {
            $this->storeManager->setCurrentStore((int) $website->getDefaultGroup()->getDefaultStoreId());

            /** @var \Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection $eventCollection */
            $eventCollection = $eventCollectionProvider();

            /** @var \Plumrocket\PrivateSale\Api\Data\EventInterface $event */
            foreach ($eventCollection as $event) {
                if ($event->isCategoryEvent()) {
                    try {
                        $category = $this->categoryRepository->get($event->getCategoryId());
                        $this->syncCategoryIsActive($category, $event, $simpleStatus);
                    } catch (NoSuchEntityException $e) {
                        continue;
                    }
                } elseif ($event->isProductEvent()) {
                    try {
                        $product = $this->productRepository->getById($event->getProductId());
                        $this->syncProductStatus($product, $event, $simpleStatus);
                    } catch (NoSuchEntityException $e) {
                        continue;
                    }
                }
            }
        }
    }

    /**
     * @return \Magento\Framework\Data\Collection\AbstractDb|\Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUpcomingEventsCollection()
    {
        return $this->eventCollectionFactory
            ->create()
            ->addStatusToCollection()
            ->addFieldToFilter('status', ['in' => [EventStatus::COMING_SOON, EventStatus::UPCOMING]])
            ->addAttributeToSelect(
                [
                    EventInterface::PRODUCT_EVENT,
                    EventInterface::CATEGORY_EVENT,
                    EventInterface::EVENT_TYPE,
                    'is_event_private',
                    'event_permissions',
                    'before_event_starts',
                ]
            )->setOrder('priority', 'ASC');
    }

    /**
     * @return \Magento\Framework\Data\Collection\AbstractDb|\Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEndedEventsCollection()
    {
        return $this->eventCollectionFactory
            ->create()
            ->addStatusToCollection()
            ->addFieldToFilter('status', ['eq' => EventStatus::ENDED])
            ->addAttributeToSelect(
                [
                    EventInterface::PRODUCT_EVENT,
                    EventInterface::CATEGORY_EVENT,
                    EventInterface::EVENT_TYPE,
                    'is_event_private',
                    'event_permissions',
                    'after_event_ends',
                ]
            )->setOrder('priority', 'ASC');
    }

    /**
     * @param \Magento\Catalog\Model\Product                  $product
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @param int                                             $simpleStatus
     */
    public function syncProductStatus(
        Product $product,
        EventInterface $event,
        int $simpleStatus
    ) {
        $currentStatus = (int) $product->getStatus();

        if ($simpleStatus === EventStatus::ACTIVE) {
            $nextStatus = ProductStatus::STATUS_ENABLED;
        } else {
            try {
                $browsingEvent = $this->getCanBrowseCatalogEntity($event, $simpleStatus);
            } catch (LocalizedException $e) {
                return;
            }

            $nextStatus = $browsingEvent ? ProductStatus::STATUS_ENABLED : ProductStatus::STATUS_DISABLED;
        }

        if ($nextStatus !== $currentStatus) {
            $product->setStatus($nextStatus);
            $product->getResource()->saveAttribute($product, 'status');
        }
    }

    /**
     * @param \Magento\Catalog\Model\Category                 $category
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @param int                                             $simpleStatus
     */
    public function syncCategoryIsActive(
        Category $category,
        EventInterface $event,
        int $simpleStatus
    ) {
        $currentIsActive = (int) $category->getIsActive();

        if ($simpleStatus === EventStatus::ACTIVE) {
            $nextIsActive = 1;
        } else {
            try {
                $browsingEvent = $this->getCanBrowseCatalogEntity($event, $simpleStatus);
            } catch (LocalizedException $e) {
                return;
            }

            $nextIsActive = $browsingEvent ? 1 : 0;
        }

        if ($nextIsActive !== $currentIsActive) {
            $category->setStoreId(0); //Always save in default scope

            $category->setIsActive($nextIsActive);
            $category->getResource()->saveAttribute($category, 'is_active');
        }
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @param int                                             $simpleStatus
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCanBrowseCatalogEntity(EventInterface $event, int $simpleStatus): bool
    {
        $isValidStatus = in_array(
            $simpleStatus,
            [EventStatus::UPCOMING, EventStatus::ENDED],
            true
        );

        if (! $isValidStatus) {
            throw new LocalizedException(__('Invalid status: "%1"', $simpleStatus));
        }

        if ($simpleStatus === EventStatus::UPCOMING) {
            $browsingEvent = $event->canMakeActionBeforeEventStarts(ConfigHelper::BROWSING_EVENT);
        } else {
            $browsingEvent = $event->canMakeActionAfterEventEnds(ConfigHelper::BROWSING_EVENT);
        }

        return $browsingEvent;
    }

    /**
     * [
     *    websiteId => [
     *        entityType => [
     *            entityId => IndexRow[]
     *        ]
     *    ]
     * ]
     *
     * @param array $syncEntities
     */
    public function massSync(array $syncEntities)
    {
        $savedStoreId = (int) $this->storeManager->getStore()->getId();

        foreach ($syncEntities as $websiteId => $indexRowsByEntityTypes) {
            $this->storeManager->setCurrentStore($this->storeManager->getWebsite($websiteId)->getDefaultGroup()
                ->getDefaultStoreId());
            $events = $this->getAllEvents($indexRowsByEntityTypes);
            foreach ($indexRowsByEntityTypes as $entityType => $indexRows) {
                if ($entityType === EventType::PRODUCT) {
                    $this->syncProductsByWebsite($indexRows, $events);
                } else {
                    $this->syncCategoriesByWebsite($indexRows, $events);
                }
            }
        }

        $this->storeManager->setCurrentStore($savedStoreId);
    }

    /**
     * @param IndexRow[]       $indexRows
     * @param EventInterface[] $events
     */
    private function syncProductsByWebsite(array $indexRows, array $events)
    {
        $productIds = array_unique(array_filter(
            array_map(static function (IndexRow $indexRow) {
                return $indexRow->getCatalogEntityId();
            }, $indexRows)
        ));

        if (! $productIds) {
            return;
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $productIds, 'in')
            ->create();
        $products = $this->productRepository->getList($searchCriteria)->getItems();

        foreach ($indexRows as $indexRow) {
            $productId = $indexRow->getCatalogEntityId();
            $product = $this->findProduct($products, $productId);
            if ($product && isset($events[$indexRow->getEventId()])) {
                $this->syncProductStatus(
                    $product,
                    $events[$indexRow->getEventId()],
                    $indexRow->getSimpleStatus()
                );
            }
        }
    }

    /**
     * @param IndexRow[]       $indexRows
     * @param EventInterface[] $events
     */
    private function syncCategoriesByWebsite(array $indexRows, array $events)
    {
        $categoryIds = array_unique(array_filter(
            array_map(static function (IndexRow $indexRow) {
                return $indexRow->getCatalogEntityId();
            }, $indexRows)
        ));

        if (! $categoryIds) {
            return;
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $categoryIds, 'in')
            ->create();
        $categories = $this->categoryList->getList($searchCriteria)->getItems();

        foreach ($indexRows as $indexRow) {
            $categoryId = $indexRow->getCatalogEntityId();
            $category = $this->findCategory($categories, $categoryId);
            if ($category && isset($events[$indexRow->getEventId()])) {
                $this->syncCategoryIsActive(
                    $category,
                    $events[$indexRow->getEventId()],
                    $indexRow->getSimpleStatus()
                );
            }
        }
    }

    /**
     * @param $indexRowsByEntityTypes
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAllEvents($indexRowsByEntityTypes): array
    {
        $eventIds = [];

        /** @var IndexRow[] $indexRows */
        foreach ($indexRowsByEntityTypes as $entityType => $indexRows) {
            foreach ($indexRows as $indexRow) {
                $eventIds[] = $indexRow->getEventId();
            }
        }

        if (! $eventIds) {
            return [];
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(EventInterface::IDENTIFIER, $eventIds, 'in')
            ->create();

        return $this->eventRepository->getList($searchCriteria)->getItems();
    }

    /**
     * @param \Magento\Catalog\Api\Data\CategoryInterface[]|\Magento\Catalog\Model\Category[] $categories
     * @param int                                                                             $categoryId
     * @return null|\Magento\Catalog\Model\Category
     */
    private function findCategory(array $categories, int $categoryId)
    {
        return $this->findModelById($categories, $categoryId);
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface[]|\Magento\Catalog\Model\Product[] $products
     * @param int   $productId
     * @return null|\Magento\Catalog\Model\Product
     */
    private function findProduct(array $products, int $productId)
    {
        // Product repository returns array with product ids as keys
        // In this case we can get product just by array key
        if (! array_key_exists(0, $products)) {
            return $products[$productId];
        }

        return $this->findModelById($products, $productId);
    }

    /**
     * @param \Magento\Catalog\Model\Category[]|\Magento\Catalog\Model\Product[] $models
     * @param int                                                                $id
     * @return null|\Magento\Catalog\Model\Category|\Magento\Catalog\Model\Product
     */
    private function findModelById(array $models, int $id)
    {
        foreach ($models as $model) {
            if ($id === (int) $model->getId()) {
                return $model;
            }
        }

        return null;
    }
}
