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

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Helper\Preview;
use Plumrocket\PrivateSale\Model\Catalog\Product\GetUsedProductIds;
use Plumrocket\PrivateSale\Model\Config\Source\EventStatus;
use Plumrocket\PrivateSale\Model\Indexer\IndexStructure;
use Plumrocket\PrivateSale\Model\Indexer\Product as ProductEventIndexer;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory;

class GetDataDirectly
{
    /**
     * @var CollectionFactory
     */
    private $eventCollectionFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var array|null
     */
    private $eventMapping;

    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\Product\GetUsedProductIds
     */
    private $getUsedProductIds;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Plumrocket\PrivateSale\Helper\Preview
     */
    private $preview;

    /**
     * GetDataDirectly constructor.
     *
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory $eventCollectionFactory
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface                    $categoryRepository
     * @param \Plumrocket\PrivateSale\Model\Catalog\Product\GetUsedProductIds             $getUsedProductIds
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                     $productRepository
     * @param \Plumrocket\PrivateSale\Helper\Preview                              $preview
     */
    public function __construct(
        CollectionFactory $eventCollectionFactory,
        CategoryRepositoryInterface $categoryRepository,
        GetUsedProductIds $getUsedProductIds,
        ProductRepositoryInterface $productRepository,
        Preview $preview
    ) {
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->getUsedProductIds = $getUsedProductIds;
        $this->productRepository = $productRepository;
        $this->preview = $preview;
    }

    /**
     * @param $columnName
     * @param $columnValue
     * @param string $indexName
     * @param null $groupBy
     * @param null $limit
     */
    public function execute(
        $columnName,
        $columnValue,
        string $indexName = ProductEventIndexer::INDEX_NAME,
        $groupBy = null,
        $limit = null
    ) {
        $this->preview->pausePreviewInfluence();

        $status = $this->getStatusByIndexName($indexName);
        $eventData = $this->getMapping()[$status] ?? [];
        $result = [];

        if ('' !== $columnValue) {
            $filteredData = array_filter($eventData, static function ($array) use ($columnName, $columnValue) {
                return $array[$columnName] == $columnValue;
            });
        } else {
            $filteredData = $eventData;
        }

        if ($limit) {
            $filteredData = array_slice($filteredData, 0, $limit);
        }

        if ($groupBy) {
            foreach ($filteredData as $data) {
                $result[$data[$groupBy]] = $data;
            }
        } else {
            $result = $filteredData;
        }

        $this->preview->continuePreviewInfluence();
        return $result;
    }

    /**
     * @return array|null
     */
    private function getMapping()
    {
        if (null === $this->eventMapping) {
            /** @var \Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection $eventCollection */
            $eventCollection = $this->eventCollectionFactory->create();
            $eventCollection->addSimpleStatusToCollection()
                ->addAttributeToSelect(
                    [
                        EventInterface::PRODUCT_EVENT,
                        EventInterface::CATEGORY_EVENT,
                        EventInterface::EVENT_TYPE,
                        'is_event_private'
                    ]
                )->setOrder('priority', 'ASC');

            /** @var EventInterface $event */
            foreach ($eventCollection as $event) {
                if ($event->isCategoryEvent()) {
                    $categoryId = $event->getCategoryId();

                    try {
                        $category = $this->categoryRepository->get($categoryId);
                        foreach ($category->getProductCollection() as $product) {
                            foreach ($this->getUsedProductIds->execute($product) as $childProductId) {
                                $this->eventMapping[$event->getStatus()][$childProductId]
                                    = $this->getEventData($event, $childProductId);
                            }
                        }
                    } catch (NoSuchEntityException $e) {
                        continue;
                    }
                } elseif ($event->isProductEvent()) {
                    $product = $this->productRepository->getById($event->getProductId());

                    foreach ($this->getUsedProductIds->execute($product) as $childProductId) {
                        $this->eventMapping[$event->getStatus()][$childProductId]
                            = $this->getEventData($event, $childProductId);
                    }
                }
            }
        }

        return $this->eventMapping;
    }

    /**
     * @param EventInterface $event
     * @param $productId
     * @return array
     */
    private function getEventData(EventInterface $event, $productId)
    {
        return [
            IndexStructure::EVENT_ID => $event->getId(),
            IndexStructure::PRODUCT_ID => $productId,
            IndexStructure::IS_PRIVATE => $event->isEventPrivate(),
        ];
    }

    /**
     * @param string $indexName
     * @return int
     */
    private function getStatusByIndexName(string $indexName)
    {
        switch ($indexName) {
            case ProductEventIndexer::UPCOMING_INDEX_NAME:
                return EventStatus::UPCOMING;
            case ProductEventIndexer::ENDED_INDEX_NAME:
                return EventStatus::ENDED;
            default:
                return EventStatus::ACTIVE;
        }
    }
}
