<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\ProductAlert;

/**
 * Product alert product salability plugin
 */
class ProductSalability
{

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Is product salable
     *
     * @var \Magento\InventorySalesApi\Api\IsProductSalableInterface
     */
    protected $isProductSalable;

    /**
     * Constructor
     *
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @param \Magento\InventorySalesApi\Api\IsProductSalableInterface $isProductSalable
     * @return void
     */
    public function __construct(
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore,
        \Magento\InventorySalesApi\Api\IsProductSalableInterface $isProductSalable
    )
    {
        $this->getStockIdByStore = $getStockIdByStore;
        $this->isProductSalable = $isProductSalable;
    }

    /**
     * Around is salable
     *
     * @param \Magento\ProductAlert\Model\ProductSalability $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Store\Api\Data\WebsiteInterface $website
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundIsSalable(
        \Magento\ProductAlert\Model\ProductSalability $subject,
        \Closure $proceed,
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Store\Api\Data\WebsiteInterface $website
    ): bool
    {
        return $this->isProductSalable->execute(
            $product->getSku(),
            $this->getStockIdByStore->execute($product->getStore())
        );
    }

}
