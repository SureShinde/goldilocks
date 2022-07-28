<?php
namespace Ecombricks\StoreInventory\Model;

use Ecombricks\StoreInventory\Model\InventorySales\GetStockByStore;
use Magento\InventorySalesApi\Api\StockResolverInterface;

class StockResolver implements StockResolverInterface
{
    /**
     * @var GetStockByStore
     */
    protected $getStockByStore;

    /**
     * @param GetStockByStore $getStockByStore
     */
    public function __construct(
        GetStockByStore $getStockByStore
    ) {
        $this->getStockByStore = $getStockByStore;
    }

    /**
     * @param string $type
     * @param string $code
     * @return \Magento\InventoryApi\Api\Data\StockInterface
     */
    public function execute(string $type, string $code): \Magento\InventoryApi\Api\Data\StockInterface
    {
        return $this->getStockByStore->execute($code);
    }
}
