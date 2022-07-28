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

namespace Plumrocket\PrivateSale\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Plumrocket\PrivateSale\Api\Data\FlashSaleInterface;

class FlashSale extends AbstractModel implements FlashSaleInterface
{
    protected $_idFieldName = 'sale_id';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * FlashSale constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\FlashSale::class);
    }

    /**
     * @inheritDoc
     */
    public function getProductId(): int
    {
        return (int) $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function getEventId(): int
    {
        return (int) $this->getData(self::EVENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function getDiscount(): float
    {
        return (float) $this->getData(self::DISCOUNT);
    }

    /**
     * @inheritDoc
     */
    public function getSalePrice(): float
    {
        return (float) $this->getData(self::SALE_PRICE);
    }

    /**
     * @inheritDoc
     */
    public function getQtyLimit(): int
    {
        return (int) $this->getData(self::QTY_LIMIT);
    }

    /**
     * @inheritDoc
     */
    public function setProductId(int $id): FlashSaleInterface
    {
        $this->setData(self::PRODUCT_ID, $id);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setEventId(int $id): FlashSaleInterface
    {
        $this->setData(self::EVENT_ID, $id);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setDiscount($discount): FlashSaleInterface
    {
        $this->setData(self::DISCOUNT, $discount);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSalePrice($salePrice): FlashSaleInterface
    {
        $this->setData(self::SALE_PRICE, $salePrice);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setQtyLimit(int $qtyLimit): FlashSaleInterface
    {
        $this->setData(self::QTY_LIMIT, $qtyLimit);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validateBeforeSave()
    {
        if ($this->getDiscount() > 100) {
            throw new CouldNotSaveException(__('Discount must be less or equal than 100'));
        }

        $product = $this->getProduct();

        if (! $product) {
            throw new CouldNotSaveException(__('There is no product with id %1', $this->getProductId()));
        }

        if (! $product->getPrice()) {
            throw new CouldNotSaveException(__('You can not use a discount for the product with id %1 because its price is 0', $this->getProductId()));
        }

        return parent::validateBeforeSave();
    }

    /**
     * @inheritDoc
     */
    public function beforeSave()
    {
        $product = $this->getProduct();

        if ($product) {
            if ($this->getSalePrice()
                && $this->getSalePrice() !== (float) $this->getOrigData(self::SALE_PRICE)
            ) {
                $discountDiff = ($this->getSalePrice() / $product->getPrice()) * 100 ;
                $this->setDiscount(100 - $discountDiff);
            } elseif ($this->getDiscount()
                && $this->getDiscount() !== (float) $this->getOrigData(self::DISCOUNT)
            ) {
                $priceDiff = ($this->getDiscount() / 100) * $product->getPrice();
                $this->setSalePrice($product->getPrice() - $priceDiff);
            } elseif (! $this->getSalePrice() && ! $this->getDiscount()) {
                $this->setSalePrice(null);
                $this->setDiscount(null);
            }
        }

        return parent::beforeSave();
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    private function getProduct()
    {
        try {
            $product = $this->productRepository->getById($this->getProductId());
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        return $product;
    }
}
