<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventoryExportStock\ResourceModel;

if (version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.2', '>=')) :

/**
 * Stock index dump processor plugin
 */
class StockIndexDumpProcessor
{

    /**
     * Connection provider
     *
     * @var \Ecombricks\Common\Model\ResourceModel\ConnectionProvider
     */
    protected $connectionProvider;

    /**
     * Get website ID by store ID
     *
     * @var \Ecombricks\StoreCommon\Model\Store\GetWebsiteIdByStoreId
     */
    protected $getWebsiteIdByStoreId;

    /**
     * Stock index table name resolver
     *
     * @var \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface
     */
    protected $stockIndexTableNameResolver;

    /**
     * Not manage stock condition
     *
     * @var \Magento\InventorySales\Model\ResourceModel\IsStockItemSalableCondition\ManageStockCondition
     */
    protected $notManageStockCondition;

    /**
     * Manage stock condition
     *
     * @var \Magento\InventoryExportStock\Model\ResourceModel\ManageStockCondition
     */
    protected $manageStockCondition;

    /**
     * Get quantity for not manage stock
     *
     * @var \Magento\InventoryExportStock\Model\GetQtyForNotManageStock
     */
    protected $getQtyForNotManageStock;

    /**
     * Module manager
     *
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Model\ResourceModel\ConnectionProvider $connectionProvider
     * @param \Ecombricks\StoreCommon\Model\Store\GetWebsiteIdByStoreId $getWebsiteIdByStoreId
     * @param \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param \Magento\InventorySales\Model\ResourceModel\IsStockItemSalableCondition\ManageStockCondition $notManageStockCondition
     * @param \Magento\InventoryExportStock\Model\ResourceModel\ManageStockCondition $manageStockCondition
     * @param \Magento\InventoryExportStock\Model\GetQtyForNotManageStock $getQtyForNotManageStock
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Ecombricks\Common\Model\ResourceModel\ConnectionProvider $connectionProvider,
        \Ecombricks\StoreCommon\Model\Store\GetWebsiteIdByStoreId $getWebsiteIdByStoreId,
        \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        \Magento\InventorySales\Model\ResourceModel\IsStockItemSalableCondition\ManageStockCondition $notManageStockCondition,
        \Magento\InventoryExportStock\Model\ResourceModel\ManageStockCondition $manageStockCondition,
        \Magento\InventoryExportStock\Model\GetQtyForNotManageStock $getQtyForNotManageStock,
        \Magento\Framework\Module\Manager $moduleManager,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->connectionProvider = $connectionProvider;
        $this->getWebsiteIdByStoreId = $getWebsiteIdByStoreId;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->notManageStockCondition = $notManageStockCondition;
        $this->manageStockCondition = $manageStockCondition;
        $this->getQtyForNotManageStock = $getQtyForNotManageStock;
        $this->moduleManager = $moduleManager;
        $this->logger = $logger;
    }

    /**
     * Get composite product types
     *
     * @return array
     */
    protected function getCompositeProductTypes(): array
    {
        return [
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
            \Magento\Bundle\Model\Product\Type::TYPE_CODE,
            \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE,
        ];
    }

    /**
     * Get stock index select
     *
     * @param int $storeId
     * @param int $stockId
     * @return \Magento\Framework\DB\Select
     */
    protected function getStockIndexSelect(int $storeId, int $stockId): \Magento\Framework\DB\Select
    {
        $connection = $this->connectionProvider->getConnection();
        $select = $this->connectionProvider->getSelect();
        return $select->from(
                ['stock_index' => $this->connectionProvider->getTable($this->stockIndexTableNameResolver->execute($stockId))],
                [
                    'qty' => $this->connectionProvider->getCheckSql(
                        $connection->quoteInto('product_entity.type_id IN (?)', $this->getCompositeProductTypes()),
                        'NULL',
                        'quantity'
                    ),
                    'is_salable' => 'is_salable',
                    'sku' => 'sku',
                ]
            )
            ->join(
                ['product_entity' => $this->connectionProvider->getTable('catalog_product_entity')],
                'product_entity.sku = stock_index.sku',
                ''
            )
            ->join(
                ['legacy_stock_item' => $this->connectionProvider->getTable('cataloginventory_stock_item')],
                'legacy_stock_item.product_id = product_entity.entity_id',
                ''
            )
            ->join(
                ['product_store' => $this->connectionProvider->getTable('ecombricks_store__catalog_product_store')],
                'legacy_stock_item.product_id = product_store.product_id',
                ''
            )
            ->where($this->manageStockCondition->execute($select))
            ->where('product_store.store_id = ?', $storeId);
    }

    /**
     * Get stock item select
     *
     * @param int $storeId
     * @return \Magento\Framework\DB\Select
     */
    protected function getStockItemSelect(int $storeId): \Magento\Framework\DB\Select
    {
        $connection = $this->connectionProvider->getConnection();
        $select = $this->connectionProvider->getSelect();
        return $select->from(
                ['legacy_stock_item' => $this->connectionProvider->getTable('cataloginventory_stock_item')],
                [
                    'qty' => $this->connectionProvider->getCheckSql(
                        $connection->quoteInto('product_entity.type_id IN (?)', $this->getCompositeProductTypes()),
                        'NULL',
                        $this->getQtyForNotManageStock->execute() ?? 'NULL'
                    ),
                    'is_salable' => $this->connectionProvider->getSql('1'),
                ]
            )
            ->join(
                ['product_entity' => $this->connectionProvider->getTable('catalog_product_entity')],
                'legacy_stock_item.product_id = product_entity.entity_id',
                ['sku']
            )
            ->join(
                ['product_store' => $this->connectionProvider->getTable('ecombricks_store__catalog_product_store')],
                'legacy_stock_item.product_id = product_store.product_id',
                ''
            )
            ->where($this->notManageStockCondition->execute($select))
            ->where('product_store.store_id = ?', $storeId);
    }

    /**
     * Around execute
     *
     * @param \Magento\InventoryExportStock\Model\ResourceModel\StockIndexDumpProcessor $subject
     * @param \Closure $proceed
     * @param int $storeId
     * @param int $stockId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundExecute(
        \Magento\InventoryExportStock\Model\ResourceModel\StockIndexDumpProcessor $subject,
        \Closure $proceed,
        int $storeId,
        int $stockId
    ): array
    {
        if (!$this->moduleManager->isEnabled('Ecombricks_StoreCatalog')) {
            return $proceed($this->getWebsiteIdByStoreId->execute($storeId), $stockId);
        }
        $select = $this->connectionProvider->getSelect();
        try {
            $select->union([
                $this->getStockItemSelect($storeId),
                $this->getStockIndexSelect($storeId, $stockId)
            ]);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage(), $exception->getTrace());
            throw new \Magento\Framework\Exception\LocalizedException(__('Something went wrong. Export couldn\'t be executed, See log files for error details'));
        }
        return $this->connectionProvider->getConnection()->fetchAll($select);
    }



}

endif;
