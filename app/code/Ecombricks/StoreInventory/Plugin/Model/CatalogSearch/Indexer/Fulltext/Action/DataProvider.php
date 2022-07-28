<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\CatalogSearch\Indexer\Fulltext\Action;

/**
 * Catalog search full text indexer data provider plugin
 */
class DataProvider extends \Ecombricks\Common\Plugin\InheritorPlugin
{

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
     * @param \Magento\InventoryElasticsearch\Plugin\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider\StockedProductFilterByInventoryStock $parent
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Magento\InventoryElasticsearch\Plugin\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider\StockedProductFilterByInventoryStock $parent,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
    )
    {
        parent::__construct($wrapperFactory);
        $this->setParent($parent);
        $this->getStockIdByStore = $getStockIdByStore;
    }

    /**
     * Before prepare product index
     *
     * @param \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider $subject
     * @param array $indexData
     * @param array $productData
     * @param int $storeId
     * @return array
     */
    public function beforePrepareProductIndex(
        \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider $subject,
        $indexData,
        $productData,
        $storeId
    )
    {
        $this->setSubject($subject);
        $stockConfiguration = $this->getParentPropertyValue('stockConfiguration');
        $defaultStockProvider = $this->getParentPropertyValue('defaultStockProvider');
        if (!$stockConfiguration->isShowOutOfStock($storeId)) {
            $productIds = array_keys($indexData);
            $stockId = $this->getStockIdByStore->execute($storeId);
            if ($defaultStockProvider->getId() === $stockId) {
                $stockStatuses = $this->invokeParentMethod('getStockStatusesFromDefaultStock', $productIds);
            } else {
                $stockStatuses = $this->invokeParentMethod('getStockStatusesFromCustomStock', $productIds, $stockId);
            }
            $indexData = array_intersect_key($indexData, $stockStatuses);
        }
        return [$indexData, $productData, $storeId];
    }

}
