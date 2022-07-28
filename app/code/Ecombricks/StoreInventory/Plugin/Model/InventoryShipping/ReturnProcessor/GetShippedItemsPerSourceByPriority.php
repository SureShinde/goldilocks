<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventoryShipping\ReturnProcessor;

/**
 * Get shipped items per source by priority plugin
 */
class GetShippedItemsPerSourceByPriority extends \Ecombricks\Common\Plugin\Plugin
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
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
    )
    {
        parent::__construct($wrapperFactory);
        $this->getStockIdByStore = $getStockIdByStore;
    }

    /**
     * Sort quantities by source priority
     *
     * @param array $qtys
     * @param int $storeId
     * @return array
     */
    protected function sortQtysBySourcePriority(array $qtys, int $storeId): array
    {
        $getSourcesAssignedToStockOrderedByPriority = $this->getSubjectPropertyValue('getSourcesAssignedToStockOrderedByPriority');
        $qtysBySourcePriority = [];
        try {
            $stockId = $this->getStockIdByStore->execute($storeId);
            $sources = $getSourcesAssignedToStockOrderedByPriority->execute($stockId);
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $sources = [];
        }
        foreach ($sources as $source) {
            $sourceCode = $source->getSourceCode();
            if (!empty($qtys[$sourceCode])) {
                $qtysBySourcePriority[$sourceCode] = $qtys[$sourceCode];
                unset($qtys[$sourceCode]);
            }
        }
        foreach ($qtys as $sourceCode => $sourceQtys) {
            $qtysBySourcePriority[$sourceCode] = $sourceQtys;
        }
        return $qtysBySourcePriority;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventoryShipping\Model\ReturnProcessor\GetShippedItemsPerSourceByPriority $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $returnToStockItems
     * @return \Magento\InventorySalesApi\Model\ReturnProcessor\Result\SourceDeductedOrderItemsResult[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundExecute(
        \Magento\InventoryShipping\Model\ReturnProcessor\GetShippedItemsPerSourceByPriority $subject,
        \Closure $proceed,
        \Magento\Sales\Api\Data\OrderInterface $order,
        array $returnToStockItems
    ): array
    {
        $this->setSubject($subject);
        $getSourceCodeByShipmentId = $this->getSubjectPropertyValue('getSourceCodeByShipmentId');
        $getItemsToDeductFromShipment = $this->getSubjectPropertyValue('getItemsToDeductFromShipment');
        $qtys = [];
        foreach ($order->getShipmentsCollection() as $shipment) {
            $sourceCode = $getSourceCodeByShipmentId->execute((int) $shipment->getId());
            $items = $getItemsToDeductFromShipment->execute($shipment);
            foreach ($items as $item) {
                $sku = $item->getSku();
                $qtys[$sourceCode][$sku] = ($qtys[$sourceCode][$sku] ?? 0) + $item->getQty();
            }
        }
        $storeId = (int) $order->getStore()->getId();
        return $this->invokeSubjectMethod(
            'getSourceDeductedItemsResult',
            $this->invokeSubjectMethod('groupItemsBySku', $this->sortQtysBySourcePriority($qtys, $storeId))
        );
    }

}
