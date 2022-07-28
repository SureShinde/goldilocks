<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Setup\Operation;

/**
 * Install data setup operation
 */
class InstallData extends \Ecombricks\Common\Setup\Operation\AbstractOperation
{

    /**
     * Stock repository
     *
     * @var \Magento\InventoryApi\Api\StockRepositoryInterface
     */
    protected $stockRepository;

    /**
     * Get default stock
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetDefaultStockInterface
     */
    protected $getDefaultStock;

    /**
     * Store sales channel factory
     *
     * @var \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory
     */
    protected $storeSalesChannelFactory;

    /**
     * Constructor
     *
     * @param \Magento\InventoryApi\Api\StockRepositoryInterface $stockRepository
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetDefaultStockInterface $getDefaultStock
     * @param \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
     * @return void
     */
    public function __construct(
        \Magento\InventoryApi\Api\StockRepositoryInterface $stockRepository,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetDefaultStockInterface $getDefaultStock,
        \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
    )
    {
        $this->stockRepository = $stockRepository;
        $this->getDefaultStock = $getDefaultStock;
        $this->storeSalesChannelFactory = $storeSalesChannelFactory;
    }

    /**
     * Execute
     *
     * @return \Ecombricks\Common\Setup\Operation\OperationInterface
     */
    public function execute(): \Ecombricks\Common\Setup\Operation\OperationInterface
    {
        $defaultStock = $this->getDefaultStock->execute();
        $defaultStockExtension = $defaultStock->getExtensionAttributes();
        $salesChannels = $defaultStockExtension->getSalesChannels();
        $salesChannels[] = $this->storeSalesChannelFactory->createByStore();
        $defaultStockExtension->setSalesChannels($salesChannels);
        $this->stockRepository->save($defaultStock);
        return $this;
    }

}
