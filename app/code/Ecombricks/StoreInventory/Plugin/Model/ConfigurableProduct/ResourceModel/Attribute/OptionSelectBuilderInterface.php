<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\ConfigurableProduct\ResourceModel\Attribute;

/**
 * Configurable product option select builder interface plugin
 */
class OptionSelectBuilderInterface
{

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
     * Stock index table name resolver
     *
     * @var \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface
     */
    protected $stockIndexTableNameResolver;

    /**
     * Default stock provider
     *
     * @var \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface
     */
    protected $defaultStockProvider;

    /**
     * Constructor
     *
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @param \Ecombricks\Common\Model\ResourceModel\ConnectionProvider $connectionProvider
     * @param \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface $defaultStockProvider
     * @return void
     */
    public function __construct(
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore,
        \Ecombricks\Common\Model\ResourceModel\ConnectionProvider $connectionProvider,
        \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface $defaultStockProvider
    )
    {
        $this->getStockIdByStore = $getStockIdByStore;
        $this->connectionProvider = $connectionProvider;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * After get select
     *
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface $subject
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetSelect(
        \Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface $subject,
        \Magento\Framework\DB\Select $select
    )
    {
        $stockId = $this->getStockIdByStore->execute();
        if ($stockId === $this->defaultStockProvider->getId()) {
            return $select;
        }
        $select
            ->joinInner(
                ['stock' => $this->stockIndexTableNameResolver->execute($stockId)],
                'stock.sku = entity.sku',
                []
            )
            ->where('stock.is_salable = ?', 1);
        return $select;
    }

}
