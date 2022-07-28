<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Pricing\InventoryConfigurableProduct\Price\LowestPriceOptionsProvider;

if (version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.4', '>=')) :

/**
 * Configurable product lowest options price provider stock status base select processor plugin
 */
class StockStatusBaseSelectProcessor
{

    /**
     * Stock configuration
     *
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfig;

    /**
     * Default stock provider
     *
     * @var \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface
     */
    protected $defaultStockProvider;

    /**
     * Stock index table name resolver
     *
     * @var \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface
     */
    protected $stockIndexTableNameResolver;

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Connection provider
     *
     * @var \Ecombricks\Common\Model\ResourceModel\ConnectionProvider
     */
    protected $connectionProvider;

    /**
     * Constructor
     *
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfig
     * @param \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface $defaultStockProvider
     * @param \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param \Ecombricks\Common\Model\ResourceModel\ConnectionProvider $connectionProvider
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfig,
        \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface $defaultStockProvider,
        \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        \Ecombricks\Common\Model\ResourceModel\ConnectionProvider $connectionProvider,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
    )
    {
        $this->stockConfig = $stockConfig;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->connectionProvider = $connectionProvider;
        $this->getStockIdByStore = $getStockIdByStore;
    }

    /**
     * Around process
     *
     * @param \Magento\InventoryConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider\StockStatusBaseSelectProcessor $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundProcess(
        \Magento\InventoryConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider\StockStatusBaseSelectProcessor $subject,
        \Closure $proceed,
        \Magento\Framework\DB\Select $select
    )
    {
        if (!$this->stockConfig->isShowOutOfStock()) {
            return $select;
        }
        $stockId = $this->getStockIdByStore->execute();
        $productTableAlias = \Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS;
        if ($stockId === $this->defaultStockProvider->getId()) {
            $stockTable = $this->connectionProvider->getTable('cataloginventory_stock_status');
            $isSalableColumnName = 'stock_status';
            $select->join(['stock' => $stockTable], 'stock.product_id = '.$productTableAlias.'.entity_id', []);
        } else {
            $stockTable = $this->stockIndexTableNameResolver->execute($stockId);
            $isSalableColumnName = \Magento\InventoryIndexer\Indexer\IndexStructure::IS_SALABLE;
            $select->join(['stock' => $stockTable], 'stock.sku = '.$productTableAlias.'.sku', []);
        }
        $select->where('stock.'.$isSalableColumnName.' = ?', 1);
        return $select;
    }

}

endif;
