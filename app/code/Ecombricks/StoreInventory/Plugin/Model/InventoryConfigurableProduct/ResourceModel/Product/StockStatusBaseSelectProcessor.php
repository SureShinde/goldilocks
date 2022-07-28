<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventoryConfigurableProduct\ResourceModel\Product;

/**
 * Stock status base select processor
 */
class StockStatusBaseSelectProcessor
{

    /**
     * Stock index table name resolver
     *
     * @var \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface
     */
    protected $stockIndexTableNameResolver;

    /**
     * Stock configuration
     *
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfig;

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Default stock provider
     *
     * @var \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface
     */
    protected $defaultStockProvider;

    /**
     * Connection provider
     *
     * @var \Ecombricks\Common\Model\ResourceModel\ConnectionProvider
     */
    protected $connectionProvider;

    /**
     * Constructor
     *
     * @param \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfig
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @param \Ecombricks\Common\Model\ResourceModel\ConnectionProvider $connectionProvider
     * @param \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface $defaultStockProvider
     * @return void
     */
    public function __construct(
        \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfig,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore,
        \Ecombricks\Common\Model\ResourceModel\ConnectionProvider $connectionProvider,
        \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface $defaultStockProvider
    )
    {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->stockConfig = $stockConfig;
        $this->getStockIdByStore = $getStockIdByStore;
        $this->connectionProvider = $connectionProvider;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * Process
     *
     * @param \Magento\InventoryConfigurableProduct\Model\ResourceModel\Product\StockStatusBaseSelectProcessor $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundProcess(
        \Magento\InventoryConfigurableProduct\Model\ResourceModel\Product\StockStatusBaseSelectProcessor $subject,
        \Closure $proceed,
        \Magento\Framework\DB\Select $select
    )
    {
        if ($this->stockConfig->isShowOutOfStock()) {
            return $select;
        }
        $productTableAlias = \Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS;
        if ($this->getStockIdByStore->execute() === $this->defaultStockProvider->getId()) {
            $isSalableColumnName = 'stock_status';
            $select->join(
                ['stock' => $this->connectionProvider->getTable('cataloginventory_stock_status')],
                sprintf('stock.product_id = %s.entity_id', $productTableAlias),
                []
            );
        } else {
            $isSalableColumnName = \Magento\InventoryIndexer\Indexer\IndexStructure::IS_SALABLE;
            $select->join(
                ['stock' => $this->stockIndexTableNameResolver->execute($this->getStockIdByStore->execute())],
                sprintf('stock.sku = %s.sku', $productTableAlias),
                []
            );
        }
        $select->where(sprintf('stock.%1s = ?', $isSalableColumnName), 1);
        return $select;
    }

}
