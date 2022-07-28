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

namespace Plumrocket\PrivateSale\Model\Inventory;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;

class SalableQuantity
{
    /**
     * @var ObjectProvider
     */
    protected $objectProvider;

    /**
     * @var StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * SalableQuantity constructor.
     *
     * @param ObjectProvider $objectProvider
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ObjectProvider $objectProvider,
        StockItemRepositoryInterface $stockItemRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->objectProvider = $objectProvider;
        $this->stockItemRepository = $stockItemRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @param $id
     * @return float|void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException
     */
    public function getById($id)
    {
        try {
            $isSourceItemManagementAllowedForProductType = $this->objectProvider
                ->getObjectIsSourceItemManagementAllowedForProductType();
            $getSalableQuantityDataBySku = $this->objectProvider->getObjectGetSalableQuantityDataBySkuObject();

            if ($getSalableQuantityDataBySku && $isSourceItemManagementAllowedForProductType) {
                if ($isSourceItemManagementAllowedForProductType->execute($id) !== true) {
                    return 0;
                }

                $product = $this->productRepository->getById($id);
                $sku = $product->getSku();
                $stockIds = $this->objectProvider->getAssignedStockIdsBySku()->execute($sku);
                $qty = 0;

                foreach ($stockIds as $stockId) {
                    $stockId = (int)$stockId;
                    $stockItemConfiguration = $this->objectProvider->getStockItemConfiguration()->execute($sku, $stockId);
                    $isManageStock = $stockItemConfiguration->isManageStock();
                    $qty += $isManageStock ? $this->objectProvider->getProductSalableQty()->execute($sku, $stockId) : 0;
                }
            } else {
                $qty = $this->stockItemRepository->get((int) $id)->getQty();
            }
        } catch (NoSuchEntityException $e) {
            return 0;
        }


        return (int) $qty;
    }
}
