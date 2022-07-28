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

namespace Plumrocket\PrivateSale\Model\Event;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Plumrocket\PrivateSale\Api\EventRepositoryInterface;
use Plumrocket\PrivateSale\Model\Catalog\Category\GetProductIds;
use Plumrocket\PrivateSale\Model\Event\Category\GetEventId as GetCategoryEventId;
use Plumrocket\PrivateSale\Model\Event\Category\GetEventIds as GetCategoriesEventIds;
use Plumrocket\PrivateSale\Model\Event\Product\GetAllEventsIds as GetAllProductsEventIds;
use Plumrocket\PrivateSale\Model\Indexer\IndexHandler;
use Plumrocket\PrivateSale\Model\Indexer\IndexStructure;
use Plumrocket\PrivateSale\Model\Indexer\Product as ProductEventIndexer;

/**
 * @since 5.0.0
 */
class Catalog
{
    /**
     * @var GetEventIdByProductId
     */
    private $getEventIdByProductId;

    /**
     * @var \Plumrocket\PrivateSale\Model\Event\Category\GetEventId
     */
    private $getEventIdByCategoryId;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var IndexHandler
     */
    private $indexHandler;

    /**
     * @var GetCategoriesEventIds
     */
    private $getCategoriesEventIds;

    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\Category\GetProductIds
     */
    private $getCategoriesProductIds;

    /**
     * @var GetAllProductsEventIds
     */
    private $getAllProductsEventIds;

    /**
     * @param \Plumrocket\PrivateSale\Model\Event\GetEventIdByProductId    $getEventIdByProductId
     * @param \Plumrocket\PrivateSale\Model\Event\Category\GetEventId      $getEventIdByCategoryId
     * @param \Plumrocket\PrivateSale\Api\EventRepositoryInterface         $eventRepository
     * @param \Plumrocket\PrivateSale\Model\Indexer\IndexHandler           $indexHandler
     * @param \Plumrocket\PrivateSale\Model\Event\Category\GetEventIds     $getCategoriesEventIds
     * @param \Plumrocket\PrivateSale\Model\Catalog\Category\GetProductIds $getCategoriesProductIds
     * @param \Plumrocket\PrivateSale\Model\Event\Product\GetAllEventsIds  $getAllProductsEventIds
     */
    public function __construct(
        GetEventIdByProductId $getEventIdByProductId,
        GetCategoryEventId $getEventIdByCategoryId,
        EventRepositoryInterface $eventRepository,
        IndexHandler $indexHandler,
        GetCategoriesEventIds $getCategoriesEventIds,
        GetProductIds $getCategoriesProductIds,
        GetAllProductsEventIds $getAllProductsEventIds
    ) {
        $this->getEventIdByProductId = $getEventIdByProductId;
        $this->getEventIdByCategoryId = $getEventIdByCategoryId;
        $this->eventRepository = $eventRepository;
        $this->indexHandler = $indexHandler;
        $this->getCategoriesEventIds = $getCategoriesEventIds;
        $this->getCategoriesProductIds = $getCategoriesProductIds;
        $this->getAllProductsEventIds = $getAllProductsEventIds;
    }

    /**
     * @param array $categoryIds
     * @param int   $status
     * @param int   $days
     * @return array
     */
    public function getByCategories(array $categoryIds, int $status, int $days): array
    {
        $categoryEventIds = $this->getCategoriesEventIds->execute($categoryIds, $status, $days);

        if ($productEventIds = $this->getProductEventIds($categoryIds, $status, $days)) {
            return array_merge($categoryEventIds, $productEventIds);
        }

        return $categoryEventIds;
    }

    /**
     * @param CategoryInterface $category
     * @return bool
     */
    public function isExistsEventForCategory(CategoryInterface $category): bool
    {
        return (bool) $this->getEventIdByCategoryId->execute((int) $category->getId());
    }

    /**
     * @param ProductInterface $product
     * @return bool
     */
    public function isExistsEventForProduct(ProductInterface $product): bool
    {
        return (bool) $this->getEventIdByProductId->execute((int) $product->getId());
    }

    /**
     * @param ProductInterface $product
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface|null
     */
    public function getEventForProduct(ProductInterface $product)
    {
        $eventId = $this->getEventIdByProductId->execute((int) $product->getId());
        return $this->getEventById($eventId);
    }

    /**
     * @param CategoryInterface $category
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface|null
     */
    public function getEventForCategory(CategoryInterface $category)
    {
         $eventId = $this->getEventIdByCategoryId->execute((int) $category->getId());
         return $this->getEventById($eventId);
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getProductIdsOnSale(int $limit): array
    {
        $indexData =  $this->indexHandler->readAll(
            IndexStructure::PRODUCT_ID,
            '',
            ProductEventIndexer::INDEX_NAME,
            IndexStructure::PRODUCT_ID,
            $limit
        );

        return array_column($indexData, IndexStructure::PRODUCT_ID);
    }

    /**
     * @param int $eventId
     * @param int|null $limit
     * @return array
     */
    public function getProductIdsByEvent(int $eventId, $limit = null): array
    {
        $indexData =  $this->indexHandler->readAll(
            IndexStructure::EVENT_ID,
            $eventId,
            ProductEventIndexer::INDEX_NAME,
            IndexStructure::PRODUCT_ID,
            $limit
        );

        return array_column($indexData, IndexStructure::PRODUCT_ID);
    }

    /**
     * @param $eventId
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface|null
     */
    private function getEventById($eventId)
    {
        try {
            $event = $eventId ? $this->eventRepository->getById($eventId) : null;
        } catch (NoSuchEntityException $e) {
            $event = null;
        }

        return $event;
    }

    /**
     * Retrieve products events in categories
     *
     * @param array $parentCategoryIds
     * @param int   $status
     * @param int   $days
     * @return array
     */
    private function getProductEventIds(array $parentCategoryIds, int $status, int $days): array
    {
        if ($allProductsEventIdsForStatus = $this->getAllProductsEventIds->execute($status, $days)) {
            $categoryAllProductIds = $this->getCategoriesProductIds->execute($parentCategoryIds);
            $productIds = array_intersect($categoryAllProductIds, array_keys($allProductsEventIdsForStatus));
            if ($productIds) {
                return array_intersect_key($allProductsEventIdsForStatus, array_flip($productIds));
            }
        }

        return [];
    }
}
