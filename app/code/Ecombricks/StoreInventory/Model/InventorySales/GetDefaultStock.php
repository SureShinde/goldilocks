<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Model\InventorySales;

/**
 * Get default stock
 */
class GetDefaultStock implements \Ecombricks\StoreInventory\Api\InventorySalesApi\GetDefaultStockInterface
{

    /**
     * Stock repository
     *
     * @var \Magento\InventoryApi\Api\StockRepositoryInterface
     */
    protected $stockRepository;

    /**
     * Default stock provider
     *
     * @var \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface
     */
    protected $defaultStockProvider;

    /**
     * Constructor
     *
     * @param \Magento\InventoryApi\Api\StockRepositoryInterface $stockRepository
     * @param \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface $defaultStockProvider
     * @return void
     */
    public function __construct(
        \Magento\InventoryApi\Api\StockRepositoryInterface $stockRepository,
        \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface $defaultStockProvider
    )
    {
        $this->stockRepository = $stockRepository;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * Execute
     *
     * @return \Magento\InventoryApi\Api\Data\StockInterface
     */
    public function execute(): \Magento\InventoryApi\Api\Data\StockInterface
    {
        return $this->stockRepository->get($this->defaultStockProvider->getId());
    }

}
