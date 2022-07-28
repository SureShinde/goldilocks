<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\CatalogInventory\Indexer;

/**
 * Catalog inventory product price index filter
 */
class ProductPriceIndexFilter extends \Ecombricks\Common\Plugin\InheritorPlugin
{

    /**
     * Default stock provider
     *
     * @var \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface
     */
    protected $defaultStockProvider;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Module manager
     *
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Connection provider
     *
     * @var \Ecombricks\Common\Model\ResourceModel\ConnectionProvider
     */
    protected $connectionProvider;

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Object\WrapperFactory $wrapperFactory
     * @param \Magento\InventoryCatalog\Plugin\CatalogInventory\Model\Indexer\ModifySelectInProductPriceIndexFilter $parent
     * @param \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface $defaultStockProvider
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Ecombricks\StoreCommon\Model\Store\ResourceModel\IndexerConnectionProvider $connectionProvider
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Magento\InventoryCatalog\Plugin\CatalogInventory\Model\Indexer\ModifySelectInProductPriceIndexFilter $parent,
        \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface $defaultStockProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Ecombricks\StoreCommon\Model\Store\ResourceModel\IndexerConnectionProvider $connectionProvider,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
    )
    {
        parent::__construct($wrapperFactory);
        $this->setParent($parent);
        $this->defaultStockProvider = $defaultStockProvider;
        $this->storeManager = $storeManager;
        $this->moduleManager = $moduleManager;
        $this->connectionProvider = $connectionProvider;
        $this->getStockIdByStore = $getStockIdByStore;
    }

    /**
     * Get store IDs by product IDs
     *
     * @param array $productIds
     * @return array
     */
    protected function getStoreIdsByProductIds(array $productIds): array
    {
        $storeIds = [];
        if ($this->moduleManager->isEnabled('Ecombricks_StoreCatalog')) {
            $select = $this->connectionProvider->getSelect()
                ->from(
                    ['product_store' => $this->connectionProvider->getTable('ecombricks_store__catalog_product_store')],
                    ['store_id']
                )
                ->where('product_store.product_id IN (?)', $productIds)
                ->distinct();
            foreach ($this->connectionProvider->getConnection()->fetchCol($select) as $storeId) {
                $storeIds[] = (int) $storeId;
            }
        } else {
            foreach ($this->invokeParentMethod('getWebsiteIdsFromProducts', $productIds) as $websiteId) {
                $websiteStoreIds = $this->storeManager->getWebsite($websiteId)->getStoreIds();
                if (empty($websiteStoreIds)) {
                    continue;
                }
                $storeIds = array_merge($storeIds, $websiteStoreIds);
            }
        }
        return array_unique($storeIds);
    }

    /**
     * Around modify price
     *
     * @param \Magento\CatalogInventory\Model\Indexer\ProductPriceIndexFilter $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure $priceTable
     * @param array $entityIds
     * @return void
     */
    public function aroundModifyPrice(
        \Magento\CatalogInventory\Model\Indexer\ProductPriceIndexFilter $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure $priceTable,
        array $entityIds = []
    )
    {
        $this->setSubject($subject);
        $stockConfiguration = $this->getParentPropertyValue('stockConfiguration');
        if ($stockConfiguration->isShowOutOfStock()) {
            return;
        }
        $stockIndexTableNameResolver = $this->getParentPropertyValue('stockIndexTableNameResolver');
        $connection = $this->connectionProvider->getConnection();
        $priceEntityField = $priceTable->getEntityField();
        foreach ($this->getStoreIdsByProductIds($entityIds) as $storeId) {
            $stockId = $this->getStockIdByStore->execute($storeId);
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            $select = $connection->select()->from(['price_index' => $priceTable->getTableName()], []);
            if ($stockId != $this->defaultStockProvider->getId()) {
                $select->joinInner(
                    ['product_entity' => $this->connectionProvider->getTable('catalog_product_entity')],
                    'product_entity.entity_id = price_index.'.$priceEntityField,
                    []
                );
                $select->joinLeft(
                    ['inventory_stock' => $stockIndexTableNameResolver->execute($stockId)],
                    'inventory_stock.sku = product_entity.sku',
                    []
                );
                $select->where('inventory_stock.is_salable = 0 OR inventory_stock.is_salable IS NULL');
            } else {
                $select->joinLeft(
                    ['stock_status' => $this->connectionProvider->getTable('cataloginventory_stock_status')],
                    'stock_status.product_id = price_index.'.$priceEntityField,
                    []
                );
                $select->where('stock_status.stock_status = 0 OR stock_status.stock_status IS NULL');
            }
            $select->where('price_index.website_id = ?', $websiteId);
            $select->where('price_index.store_id = ?', $storeId);
            $select->where('price_index.'.$priceEntityField.' IN (?)', $entityIds);
            $connection->query($select->deleteFromSelect('price_index'));
        }
    }

}
