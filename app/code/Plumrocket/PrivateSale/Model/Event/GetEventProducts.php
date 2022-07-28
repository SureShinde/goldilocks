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

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Api\EventRepositoryInterface;
use Plumrocket\PrivateSale\Model\Catalog\Category\GetProductIds;
use Plumrocket\PrivateSale\Model\Catalog\Product\GetUsedProductIds;
use Plumrocket\PrivateSale\Model\Store\FrontendWebsites;

/**
 * @since 5.0.0
 */
class GetEventProducts
{
    /**
     * @var \Plumrocket\PrivateSale\Api\EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\Category\GetProductIds
     */
    private $getCategoryProductIds;

    /**
     * @var \Plumrocket\PrivateSale\Model\Store\FrontendWebsites
     */
    private $frontendWebsites;

    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\Product\GetUsedProductIds
     */
    private $getUsedProductIds;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Plumrocket\PrivateSale\Api\EventRepositoryInterface            $eventRepository
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param \Plumrocket\PrivateSale\Model\Catalog\Category\GetProductIds    $getCategoryProductIds
     * @param \Plumrocket\PrivateSale\Model\Store\FrontendWebsites            $frontendWebsites
     * @param \Plumrocket\PrivateSale\Model\Catalog\Product\GetUsedProductIds $getUsedProductIds
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                 $productRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                    $searchCriteriaBuilder
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        StoreManagerInterface $storeManager,
        GetProductIds $getCategoryProductIds,
        FrontendWebsites $frontendWebsites,
        GetUsedProductIds $getUsedProductIds,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->eventRepository = $eventRepository;
        $this->storeManager = $storeManager;
        $this->getCategoryProductIds = $getCategoryProductIds;
        $this->frontendWebsites = $frontendWebsites;
        $this->getUsedProductIds = $getUsedProductIds;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @return array
     */
    public function execute(EventInterface $event): array
    {
        if ($this->storeManager->isSingleStoreMode()) {
            return $this->getFromEvent($event);
        }

        $productIds = [];
        foreach ($this->frontendWebsites->getList() as $website) {
            $productIds[] = $this->executeByWebsite($event->getId(), $website);
        }

        return $productIds ? array_unique(array_merge(...$productIds)) : [];
    }

    /**
     * @param int                                      $eventId
     * @param \Magento\Store\Api\Data\WebsiteInterface $website
     * @return array
     */
    public function executeByWebsite(int $eventId, WebsiteInterface $website): array
    {
        try {
            $event = $this->eventRepository->getById($eventId, $website->getDefaultGroup()->getDefaultStoreId());
        } catch (NoSuchEntityException $e) {
            return [];
        }

        return $this->getFromEvent($event, (int) $website->getDefaultGroup()->getDefaultStoreId());
    }

    /**
     * @param \Magento\Store\Api\Data\WebsiteInterface|null $forWebsite
     * @return array
     */
    public function executeAll(WebsiteInterface $forWebsite = null): array
    {
        if ($forWebsite) {
            $websites = [$forWebsite];
        } else {
            $websites = $this->frontendWebsites->getList();
        }

        $previousStoreId = (int) $this->storeManager->getStore()->getId();

        $productIds = [];
        foreach ($websites as $website) {
            $this->storeManager->setCurrentStore((int) $website->getDefaultGroup()->getDefaultStoreId());

            $this->searchCriteriaBuilder->addFilter(EventInterface::IS_ENABLED, 1);
            $searchResult = $this->eventRepository->getList($this->searchCriteriaBuilder->create());
            foreach ($searchResult->getItems() as $event) {
                $productIds[] = $this->getFromEvent($event, (int) $website->getDefaultGroup()->getDefaultStoreId());
            }
        }

        $this->storeManager->setCurrentStore($previousStoreId);

        return $productIds ? array_unique(array_merge(...$productIds)) : [];
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @param int|null                                        $storeId
     * @return array
     */
    private function getFromEvent(EventInterface $event, int $storeId = null): array
    {
        if ($event->isProductEvent()) {
            try {
                $product = $this->productRepository->getById($event->getProductId(), false, $storeId);
                return $this->getUsedProductIds->execute($product);
            } catch (NoSuchEntityException $e) {
                return [];
            }
        }

        $categoryProductIds = $this->getCategoryProductIds->execute([$event->getCategoryId()]);

        $this->searchCriteriaBuilder->addFilter('entity_id', $categoryProductIds, 'in');
        $searchResult = $this->productRepository->getList($this->searchCriteriaBuilder->create());

        $productIds = [];
        foreach ($searchResult->getItems() as $product) {
            $productIds[] = $this->getUsedProductIds->execute($product);
        }

        return $productIds ? array_merge(...$productIds) : [];
    }
}
