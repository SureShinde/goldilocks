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

namespace Plumrocket\PrivateSale\Plugin\Preview;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Exception\NoSuchEntityException;
use Plumrocket\PrivateSale\Api\Data\FlashSaleInterface;
use Plumrocket\PrivateSale\Api\EventRepositoryInterface;
use Plumrocket\PrivateSale\Api\GetEventIdByProductIdInterface;
use Plumrocket\PrivateSale\Helper\Preview as PreviewHelper;
use Plumrocket\PrivateSale\Model\Frontend\ProductEventPermissions;
use Plumrocket\PrivateSale\Model\ResourceModel\FlashSale\CollectionFactory as FlashSaleCollectionFactory;
use Plumrocket\PrivateSale\Model\ResourceModel\SpecialPriceStorage;
use Plumrocket\PrivateSale\Model\SpecialPriceStorageFactory;

/**
 * Define status, special price and other product's attributes during event preview
 *
 * @since v5.0.0
 */
class ManageProductsAttributes
{
    /**
     * Local cache for product's flash sales
     *
     * @var array
     */
    private $flashSales = [];

    /**
     * Local cache for original special prices
     *
     * @var array
     */
    private $specialPrices = [];

    /**
     * @var \Plumrocket\PrivateSale\Helper\Preview
     */
    private $previewHelper;

    /**
     * @var \Plumrocket\PrivateSale\Model\Frontend\ProductEventPermissions
     */
    private $productEventPermissions;

    /**
     * @var \Plumrocket\PrivateSale\Api\GetEventIdByProductIdInterface
     */
    private $getEventIdByProductId;

    /**
     * @var \Plumrocket\PrivateSale\Model\ResourceModel\FlashSale\CollectionFactory
     */
    private $flashSaleCollectionFactory;

    /**
     * @var \Plumrocket\PrivateSale\Api\EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var \Plumrocket\PrivateSale\Model\ResourceModel\SpecialPriceStorage
     */
    private $specialPriceStorageResource;

    /**
     * @var \Plumrocket\PrivateSale\Model\SpecialPriceStorageFactory
     */
    private $specialPriceStorageFactory;

    /**
     * ManageProductStatus constructor.
     *
     * @param \Plumrocket\PrivateSale\Helper\Preview                                  $previewHelper
     * @param \Plumrocket\PrivateSale\Model\Frontend\ProductEventPermissions          $productEventPermissions
     * @param \Plumrocket\PrivateSale\Api\GetEventIdByProductIdInterface              $getEventIdByProductId
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\FlashSale\CollectionFactory $flashSaleCollectionFactory
     * @param \Plumrocket\PrivateSale\Api\EventRepositoryInterface                    $eventRepository
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\SpecialPriceStorage         $specialPriceStorageResource
     * @param \Plumrocket\PrivateSale\Model\SpecialPriceStorageFactory                $specialPriceStorageFactory
     */
    public function __construct(
        PreviewHelper $previewHelper,
        ProductEventPermissions $productEventPermissions,
        GetEventIdByProductIdInterface $getEventIdByProductId,
        FlashSaleCollectionFactory $flashSaleCollectionFactory,
        EventRepositoryInterface $eventRepository,
        SpecialPriceStorage $specialPriceStorageResource,
        SpecialPriceStorageFactory $specialPriceStorageFactory
    ) {
        $this->previewHelper = $previewHelper;
        $this->productEventPermissions = $productEventPermissions;
        $this->getEventIdByProductId = $getEventIdByProductId;
        $this->flashSaleCollectionFactory = $flashSaleCollectionFactory;
        $this->eventRepository = $eventRepository;
        $this->specialPriceStorageResource = $specialPriceStorageResource;
        $this->specialPriceStorageFactory = $specialPriceStorageFactory;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $subject
     * @param                                            $result
     */
    public function afterGetStatus(ProductInterface $subject, $result)
    {
        if ($subject->getId() && $this->previewHelper->isAllowToChangeData()) {
            return $this->productEventPermissions->canBrowse((int) $subject->getId())
                ? Status::STATUS_ENABLED
                : Status::STATUS_DISABLED;
        }

        return $result;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $subject
     * @param                                            $result
     */
    public function afterGetSpecialPrice(ProductInterface $subject, $result)
    {
        if ($subject->getId() && $this->previewHelper->isAllowToChangeData()) {
            if (($flashSale = $this->getFlashSaleForByProductId((int) $subject->getId()))
                && $flashSale->getSalePrice()
            ) {
                return $flashSale->getSalePrice();
            }

            if ($specialPrice = $this->getOriginSpecialPriceByProductId($subject->getSku())) {
                if ($specialPrice->getSpecialPriceValue()) {
                    return $specialPrice->getSpecialPriceValue();
                }
                return null;
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $subject
     * @param                                            $result
     */
    public function afterGetSpecialFromDate(ProductInterface $subject, $result)
    {
        if ($subject->getId() && $this->previewHelper->isAllowToChangeData()) {
            if ($event = $this->getEvent((int) $subject->getId())) {
                return $event->getActiveFrom();
            }

            if ($specialPrice = $this->getOriginSpecialPriceByProductId($subject->getSku())) {
                return $specialPrice->getDateFrom();
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $subject
     * @param                                            $result
     */
    public function afterGetSpecialToDate(ProductInterface $subject, $result)
    {
        if ($subject->getId() && $this->previewHelper->isAllowToChangeData()) {
            if ($event = $this->getEvent((int) $subject->getId())) {
                return $event->getActiveTo();
            }

            if ($specialPrice = $this->getOriginSpecialPriceByProductId($subject->getSku())) {
                return $specialPrice->getDateTo();
            }
        }

        return $result;
    }

    /**
     * @param int $productId
     * @return \Plumrocket\PrivateSale\Api\Data\FlashSaleInterface|null
     */
    private function getFlashSaleForByProductId(int $productId)
    {
        if (! isset($this->flashSales[$productId])) {
            if ($event = $this->getEvent($productId)) {
                /** @var \Plumrocket\PrivateSale\Model\ResourceModel\FlashSale\Collection $flashSaleCollection */
                $flashSaleCollection = $this->flashSaleCollectionFactory->create();

                $flashSaleCollection->addFieldToFilter(FlashSaleInterface::EVENT_ID, $event->getId())
                                    ->addFieldToFilter(FlashSaleInterface::PRODUCT_ID, $productId)
                                    ->setPageSize(1);

                $flashSale = $flashSaleCollection->getFirstItem();

                $this->flashSales[$productId] = $flashSale->getId() ? $flashSale : null;
            } else {
                $this->flashSales[$productId] = null;
            }
        }

        return $this->flashSales[$productId];
    }

    /**
     * @param int $productId
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface|null
     */
    private function getEvent(int $productId)
    {
        try {
            return $this->eventRepository->getById($this->getEventIdByProductId->execute($productId));
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param string $sku
     * @return \Plumrocket\PrivateSale\Model\SpecialPriceStorage|null
     */
    private function getOriginSpecialPriceByProductId(string $sku)
    {
        if (! isset($this->specialPrices[$sku])) {
            /** @var \Plumrocket\PrivateSale\Model\SpecialPriceStorage $specialPrice */
            $specialPrice = $this->specialPriceStorageFactory->create();
            $this->specialPriceStorageResource->load($specialPrice, $sku, 'sku');

            $this->specialPrices[$sku] = $specialPrice->getId() ? $specialPrice : null;
        }

        return $this->specialPrices[$sku];
    }
}
