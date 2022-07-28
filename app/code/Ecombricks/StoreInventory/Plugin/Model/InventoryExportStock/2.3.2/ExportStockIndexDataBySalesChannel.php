<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventoryExportStock;

if (version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.2', '>=')) :

/**
 * Export stock index data by sales channel plugin
 */
class ExportStockIndexDataBySalesChannel
{

    /**
     * Stock index dump processor
     *
     * @var \Magento\InventoryExportStock\Model\ResourceModel\StockIndexDumpProcessor
     */
    protected $stockIndexDumpProcessor;

    /**
     * Get store ID by store code
     *
     * @var \Ecombricks\StoreInventory\Model\InventorySales\ResourceModel\GetStoreIdByStoreCode
     */
    protected $getStoreIdByStoreCode;

    /**
     * Product stock indexDataMapper
     *
     * @var \Magento\InventoryExportStock\Model\ProductStockIndexDataMapper
     */
    protected $productStockIndexDataMapper;

    /**
     * Get stock by sales channel
     *
     * @var \Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface
     */
    protected $getStockBySalesChannel;

    /**
     * Constructor
     *
     * @param \Magento\InventoryExportStock\Model\ResourceModel\StockIndexDumpProcessor $stockIndexDumpProcessor
     * @param \Ecombricks\StoreInventory\Model\InventorySales\ResourceModel\GetStoreIdByStoreCode $getStoreIdByStoreCode
     * @param \Magento\InventoryExportStock\Model\ProductStockIndexDataMapper $productStockIndexDataMapper
     * @param \Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface $getStockBySalesChannel
     * @return void
     */
    public function __construct(
        \Magento\InventoryExportStock\Model\ResourceModel\StockIndexDumpProcessor $stockIndexDumpProcessor,
        \Ecombricks\StoreInventory\Model\InventorySales\ResourceModel\GetStoreIdByStoreCode $getStoreIdByStoreCode,
        \Magento\InventoryExportStock\Model\ProductStockIndexDataMapper $productStockIndexDataMapper,
        \Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface $getStockBySalesChannel
    )
    {
        $this->stockIndexDumpProcessor = $stockIndexDumpProcessor;
        $this->getStoreIdByStoreCode = $getStoreIdByStoreCode;
        $this->productStockIndexDataMapper = $productStockIndexDataMapper;
        $this->getStockBySalesChannel = $getStockBySalesChannel;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventoryExportStock\Model\ExportStockIndexDataBySalesChannel $subject
     * @param \Closure $proceed
     * @param \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel
     * @return \Magento\InventoryExportStockApi\Api\Data\ProductStockIndexDataInterface[]
     */
    public function aroundExecute(
        \Magento\InventoryExportStock\Model\ExportStockIndexDataBySalesChannel $subject,
        \Closure $proceed,
        \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel
    ): array
    {
        $items = $this->stockIndexDumpProcessor->execute(
            $this->getStoreIdByStoreCode->execute($salesChannel->getCode()),
            $this->getStockBySalesChannel->execute($salesChannel)->getStockId()
        );
        $data = [];
        foreach ($items as $item) {
            $data[] = $this->productStockIndexDataMapper->execute($item);
        }
        return $data;
    }

}

endif;
