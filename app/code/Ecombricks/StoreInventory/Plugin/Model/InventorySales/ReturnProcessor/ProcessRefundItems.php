<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventorySales\ReturnProcessor;

/**
 * Process refund items plugin
 */
class ProcessRefundItems extends \Ecombricks\Common\Plugin\Plugin
{

    /**
     * Store sales channel factory
     *
     * @var \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory
     */
    protected $storeSalesChannelFactory;

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Object\WrapperFactory $wrapperFactory
     * @param \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
    )
    {
        parent::__construct($wrapperFactory);
        $this->storeSalesChannelFactory = $storeSalesChannelFactory;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventorySales\Model\ReturnProcessor\ProcessRefundItems $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\InventorySalesApi\Model\ReturnProcessor\Request\ItemsToRefundInterface[] $itemsToRefund
     * @param array $returnToStockItems
     * @return void
     */
    public function aroundExecute(
        \Magento\InventorySales\Model\ReturnProcessor\ProcessRefundItems $subject,
        \Closure $proceed,
        \Magento\Sales\Api\Data\OrderInterface $order,
        array $itemsToRefund,
        array $returnToStockItems
    )
    {
        $this->setSubject($subject);
        $getSourceDeductedOrderItems = $this->getSubjectPropertyValue('getSourceDeductedOrderItems');
        $itemsToSellFactory = $this->getSubjectPropertyValue('itemsToSellFactory');
        $itemToDeductFactory = $this->getSubjectPropertyValue('itemToDeductFactory');
        $salesEventFactory = $this->getSubjectPropertyValue('salesEventFactory');
        $sourceDeductionRequestFactory = $this->getSubjectPropertyValue('sourceDeductionRequestFactory');
        $sourceDeductionService = $this->getSubjectPropertyValue('sourceDeductionService');
        $placeReservationsForSalesEvent = $this->getSubjectPropertyValue('placeReservationsForSalesEvent');
        $deductedItems = $getSourceDeductedOrderItems->execute($order, $returnToStockItems);
        $itemToSell = $backItemsPerSource = [];
        foreach ($itemsToRefund as $item) {
            $sku = $item->getSku();
            $totalDeductedQty = $this->invokeSubjectMethod('getTotalDeductedQty', $item, $deductedItems);
            $processedQty = $item->getProcessedQuantity() - $totalDeductedQty;
            $qtyBackToSource = ($processedQty > 0) ? $item->getQuantity() - $processedQty : $item->getQuantity();
            $qtyBackToStock = ($qtyBackToSource > 0) ? $item->getQuantity() - $qtyBackToSource : $item->getQuantity();
            if ($qtyBackToStock > 0) {
                $itemToSell[] = $itemsToSellFactory->create(['sku' => $sku, 'qty' => (float) $qtyBackToStock]);
            }
            foreach ($deductedItems as $deductedItemResult) {
                $sourceCode = $deductedItemResult->getSourceCode();
                foreach ($deductedItemResult->getItems() as $deductedItem) {
                    if ($sku != $deductedItem->getSku() || $this->invokeSubjectMethod('isZero', (float) $qtyBackToSource)) {
                        continue;
                    }
                    $backQty = min($deductedItem->getQuantity(), $qtyBackToSource);
                    $backItemsPerSource[$sourceCode][] = $itemToDeductFactory->create(['sku' => $deductedItem->getSku(), 'qty' => -$backQty]);
                    $qtyBackToSource -= $backQty;
                }
            }
        }
        $salesChannel = $this->storeSalesChannelFactory->createByStore($order->getStore());
        $salesEvent = $salesEventFactory->create([
            'type' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::EVENT_CREDITMEMO_CREATED,
            'objectType' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId' => (string) $order->getEntityId()
        ]);
        foreach ($backItemsPerSource as $sourceCode => $items) {
            $sourceDeductionRequest = $sourceDeductionRequestFactory->create([
                'sourceCode' => $sourceCode,
                'items' => $items,
                'salesChannel' => $salesChannel,
                'salesEvent' => $salesEvent
            ]);
            $sourceDeductionService->execute($sourceDeductionRequest);
        }
        $placeReservationsForSalesEvent->execute($itemToSell, $salesChannel, $salesEvent);
    }

}
