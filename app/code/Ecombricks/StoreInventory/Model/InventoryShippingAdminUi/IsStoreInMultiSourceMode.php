<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Model\InventoryShippingAdminUi;

/**
 * Is store in multi source mode
 */
class IsStoreInMultiSourceMode
{

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Search criteria builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Get stock source links
     *
     * @var \Magento\InventoryApi\Api\GetStockSourceLinksInterface
     */
    protected $getStockSourceLinks;

    /**
     * Constructor
     *
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\InventoryApi\Api\GetStockSourceLinksInterface $getStockSourceLinks
     * @return void
     */
    public function __construct(
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\InventoryApi\Api\GetStockSourceLinksInterface $getStockSourceLinks
    )
    {
        $this->getStockIdByStore = $getStockIdByStore;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->getStockSourceLinks = $getStockSourceLinks;
    }

    /**
     * Execute
     *
     * @param int $storeId
     * @return bool
     */
    public function execute(int $storeId): bool
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(
                \Magento\InventoryApi\Api\Data\StockSourceLinkInterface::STOCK_ID,
                $this->getStockIdByStore->execute($storeId)
            )
            ->create();
        return $this->getStockSourceLinks->execute($searchCriteria)->getTotalCount() > 1;
    }

}
