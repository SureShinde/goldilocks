<?php
namespace Ecombricks\StoreInventory\Plugin\Model;

use Amasty\StorePickupWithLocatorMSI\Model\StockIdResolver;
use Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockByStoreInterface;

class StockIdResolverPlugin
{

    /** @var GetStockByStoreInterface  */
    protected $getStockByStore;

    /**
     * @param GetStockByStoreInterface $getStockByStore
     */
    public function __construct(
        GetStockByStoreInterface $getStockByStore
    ) {
        $this->getStockByStore = $getStockByStore;
    }

    /**
     * @param StockIdResolver $subject
     * @param $result
     * @param $storeId
     *
     * @return int|null
     */
    public function afterGetStockId(
        StockIdResolver $subject,
        $result,
        $storeId
    ) {
        $stock = $this->getStockByStore->execute($storeId);
        return $stock->getStockId();
    }
}