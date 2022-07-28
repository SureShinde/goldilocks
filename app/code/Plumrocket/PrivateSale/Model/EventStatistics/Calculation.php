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

namespace Plumrocket\PrivateSale\Model\EventStatistics;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\OrderRepository;
use Plumrocket\PrivateSale\Model\EventStatistics;
use Plumrocket\PrivateSale\Model\Indexer\EventStatistic\Structure;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory as EventCollectionFactory;
use Plumrocket\PrivateSale\Model\ResourceModel\EventStatistics\CollectionFactory as StatisticsCollectionFactory;

/**
 * @since 5.0.0
 */
class Calculation
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var StatisticsCollectionFactory
     */
    private $collection;

    /**
     * @var EventCollectionFactory
     */
    private $eventCollectionFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param \Magento\Sales\Model\OrderRepository                                          $orderRepository
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\EventStatistics\CollectionFactory $collection
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory           $eventCollectionFactory
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface                              $categoryRepository
     * @param \Magento\Catalog\Model\ProductRepository                                      $productRepository
     */
    public function __construct(
        OrderRepository $orderRepository,
        StatisticsCollectionFactory $collection,
        EventCollectionFactory $eventCollectionFactory,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepository $productRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->collection = $collection;
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @param array $events
     * @return array
     */
    public function calculateByEvents(array $events): array
    {
        $result = [];
        foreach ($events as $event) {
            $eventId = $event->getId();
            $data = [];

            $data[Structure::NEW_USERS] = $this->collectEventNewUsers($eventId);
            $data[Structure::ORDER_COUNT] = $this->collectEventOrderCount($eventId);
            try {
                $data[Structure::TOTAL_REVENUE] = $this->collectEventTotalRevenue($eventId);
            } catch (NoSuchEntityException $e) {
                $data[Structure::TOTAL_REVENUE] = 0;
            }

            if (array_filter($data)) {
                $data[Structure::TYPE] = EventStatistics::EVENT_TYPE;
                $result[$eventId] = $data;
            }
        }

        return $result;
    }

    /**
     * @param array $homepages
     * @return array
     */
    public function calculateByHomepages(array $homepages): array
    {
        $result = [];
        foreach ($homepages as $homepage) {
            $categoryId = (int) $homepage['entity_id'];
            $data = [];

            try {
                $data[Structure::NEW_USERS] = $this->collectHomepageNewUsers($categoryId);
                $data[Structure::ORDER_COUNT] = $this->collectHomepageOrderCount($categoryId);
                try {
                    $data[Structure::TOTAL_REVENUE] = $this->collectHomepageTotalRevenue($categoryId);
                } catch (NoSuchEntityException $e) {
                    $data[Structure::TOTAL_REVENUE] = 0;
                }
            } catch (NoSuchEntityException $e) {
                $data = [];
            }

            if (array_filter($data)) {
                $data[Structure::TYPE] = EventStatistics::HOMEPAGE_TYPE;
                $result[$categoryId] = $data;
            }
        }

        return $result;
    }

    /**
     * @param int $categoryId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function collectHomepageOrderCount(int $categoryId): int
    {
        return $this->collectEventOrderCount($this->getCategoryEventIds($categoryId));
    }

    /**
     * @param int|array $eventId
     * @return int
     */
    private function collectEventOrderCount($eventId): int
    {
        /** @var \Plumrocket\PrivateSale\Model\ResourceModel\EventStatistics\Collection $statistics */
        $statistics = $this->collection->create()
            ->addFieldToFilter('event_id', is_array($eventId) ? ['in' => $eventId] : $eventId)
            ->addFieldToFilter('order_id', ['neq' => 0]);

        return (int) $statistics->getSize();
    }

    /**
     * @param int|array $eventId
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function collectEventTotalRevenue($eventId)
    {
        /** @var \Plumrocket\PrivateSale\Model\ResourceModel\EventStatistics\Collection $statistics */
        $statistics = $this->collection->create()
            ->addFieldToFilter('event_id', is_array($eventId) ? ['in' => $eventId] : $eventId)
            ->addFieldToFilter('order_id', ['neq' => 0]);

        $totalRevenue = 0;
        foreach ($statistics->getItems() as $item) {
            $order = $this->orderRepository->get($item->getOrderId());
            if ($order) {
                foreach ($order->getAllVisibleItems() as $orderItem) {
                    if ($orderItem->getProductId() == $item->getItemId()) {
                        $totalRevenue += $orderItem->getRowTotal();
                    }
                }
            }
        }

        return $totalRevenue;
    }

    /**
     * @param int $categoryId
     * @return float|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function collectHomepageTotalRevenue(int $categoryId)
    {
        return $this->collectEventTotalRevenue($this->getCategoryEventIds($categoryId));
    }

    /**
     * @param int $categoryId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function collectHomepageNewUsers(int $categoryId): int
    {
        return $this->collectEventNewUsers($this->getCategoryEventIds($categoryId));
    }

    /**
     * @param int|array $eventId
     * @return int
     */
    private function collectEventNewUsers($eventId): int
    {
        /** @var \Plumrocket\PrivateSale\Model\ResourceModel\EventStatistics\Collection $statistics */
        $statistics = $this->collection->create()
            ->addFieldToFilter('event_id', is_array($eventId) ? ['in' => $eventId] : $eventId)
            ->addFieldToFilter('customer_id', ['neq' => 0]);

        return count(array_unique($statistics->getColumnValues('customer_id')));
    }

    /**
     * @param int $categoryId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCategoryEventIds(int $categoryId): array
    {
        $category = $this->categoryRepository->get($categoryId);
        $categoryIds = $category->getAllChildren(true);
        $singleEventIds = [];

        foreach ($this->getSingleProductIds() as $key => $item) {
            $product = $this->productRepository->getById($item['product_event']);
            if ($product && ($productCategoryIds = $product->getCategoryIds())
                && array_intersect($categoryIds, $productCategoryIds)
            ) {
                $singleEventIds[] = $key;
            }
        }

        return array_merge($singleEventIds, $this->getEventIdsByCategoryIds($categoryIds));
    }

    /**
     * @param $ids
     * @return array
     */
    private function getEventIdsByCategoryIds($ids)
    {
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        $events = $this->eventCollectionFactory->create()
            ->addFieldToFilter('category_event', ['in' => $ids]);

        return $events->getAllIds();
    }

    /**
     * @return array
     */
    private function getSingleProductIds()
    {
        $events = $this->eventCollectionFactory->create()
            ->addFieldToFilter('product_event', ['neq' => null]);

        return $events->toArray(['product_event']);
    }
}
