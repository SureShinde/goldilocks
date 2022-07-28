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

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Plumrocket\PrivateSale\Model\Event;
use Plumrocket\PrivateSale\Model\EventRepository;

class Homepage
{
    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Homepage constructor.
     * @param EventRepository $eventRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        EventRepository $eventRepository,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @param int $eventId
     * @return string
     */
    public function getNamesByEventId(int $eventId): string
    {
        try {
            $event = $this->eventRepository->getById($eventId);
        } catch (NoSuchEntityException $e) {
            return '';
        }

        $homepagesNames = [];

        if ($event->isCategoryEvent() && $categoryId = $event->getCategoryId()) {
            try {
                $category = $this->categoryRepository->get($categoryId);
                if ($category->getDisplayMode() === Event::DM_HOMEPAGE) {
                    $homepagesNames[] = $category->getName();
                }

                $homepagesNames = $this->getCategoryHomepagesList($homepagesNames, $category);
            } catch (NoSuchEntityException $e) {
                return '';
            }
        }

        if ($event->isProductEvent() && $productId = $event->getProductId()) {
            try {
                $product = $this->productRepository->getById($productId);

                foreach ($product->getCategoryIds() as $categoryId) {
                    $category = $this->categoryRepository->get($categoryId);

                    if ($category->getDisplayMode() === Event::DM_HOMEPAGE) {
                        $homepagesNames[] = $category->getName();
                    }
                }
            } catch (NoSuchEntityException $e) {
                return '';
            }
        }

        return $homepagesNames ? implode(', ', $homepagesNames) : '';
    }

    /**
     * @param array                                       $homepagesNames
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     * @return array
     */
    private function getCategoryHomepagesList(array $homepagesNames, $category): array
    {
        /**
         * "getParentCategory" throw exception when category hasn't parent, e.g. root category
         *
         * @var \Magento\Catalog\Model\Category|false $parentCategory
         */
        $parentCategory = $category->getParentId() ? $category->getParentCategory() : false;

        if ($parentCategory) {
            if ($parentCategory->getDisplayMode() === Event::DM_HOMEPAGE) {
                $homepagesNames[] = $parentCategory->getName();
            }

            $homepagesNames = $this->getCategoryHomepagesList($homepagesNames, $parentCategory);
        }

        return $homepagesNames;
    }
}
