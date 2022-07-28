<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventorySales\ReturnProcessor;

/**
 * Get invoiced items per source by priority plugin
 */
class GetInvoicedItemsPerSourceByPriority extends \Ecombricks\Common\Plugin\Plugin
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
     * Get source deducted invoice items result
     *
     * @param array $qtys
     * @param int $storeId
     * @return \Magento\InventorySalesApi\Model\ReturnProcessor\Result\SourceDeductedOrderItemsResult[]
     */
    protected function getSourceDeductedInvoiceItemsResult(array $qtys, int $storeId): array
    {
        $sourceDeductedOrderItemFactory = $this->getSubjectPropertyValue('sourceDeductedOrderItemFactory');
        $sourceDeductedOrderItemsResultFactory = $this->getSubjectPropertyValue('sourceDeductedOrderItemsResultFactory');
        $items = $result = [];
        $stockId = $this->getStockIdByStore->execute($storeId);
        foreach ($qtys as $sku => $qty) {
            $sourceCode = $this->invokeSubjectMethod('getSourceCodeWithHighestPriorityBySku', $sku, $stockId);
            $items[$sourceCode][] = $sourceDeductedOrderItemFactory->create(['sku' => $sku, 'quantity' => $qty]);
        }
        foreach ($items as $sourceCode => $sourceItems) {
            $result[] = $sourceDeductedOrderItemsResultFactory->create(['sourceCode' => $sourceCode, 'items' => $sourceItems]);
        }
        return $result;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventorySales\Model\ReturnProcessor\GetInvoicedItemsPerSourceByPriority $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $returnToStockItems
     * @return \Magento\InventorySalesApi\Model\ReturnProcessor\Result\SourceDeductedOrderItemsResult[]
     */
    public function aroundExecute(
        \Magento\InventorySales\Model\ReturnProcessor\GetInvoicedItemsPerSourceByPriority $subject,
        \Closure $proceed,
        \Magento\Sales\Api\Data\OrderInterface $order,
        array $returnToStockItems
    ): array
    {
        $this->setSubject($subject);
        $getSkuFromOrderItem = $this->getSubjectPropertyValue('getSkuFromOrderItem');
        $qtys = [];
        foreach ($order->getInvoiceCollection() as $invoice) {
            foreach ($invoice->getItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($this->invokeSubjectMethod('isValidItem', $orderItem, $returnToStockItems)) {
                    $sku = $getSkuFromOrderItem->execute($orderItem);
                    $qtys[$sku] = ($qtys[$sku] ?? 0) + $item->getQty();
                }
            }
        }
        $storeId = (int) $order->getStore()->getId();
        return $this->getSourceDeductedInvoiceItemsResult($qtys, $storeId);
    }

}
