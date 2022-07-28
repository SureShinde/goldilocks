<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventoryShipping;

/**
 * Source deduction requests from source selection factory plugin
 */
class SourceDeductionRequestsFromSourceSelectionFactory
{

    /**
     * Source deduction request factory
     *
     * @var \Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterfaceFactory
     */
    protected $sourceDeductionRequestFactory;

    /**
     * Item to deduct factory
     *
     * @var \Magento\InventorySourceDeductionApi\Model\ItemToDeductInterfaceFactory
     */
    protected $itemToDeductFactory;

    /**
     * Store sales channel factory
     *
     * @var \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory
     */
    protected $storeSalesChannelFactory;

    /**
     * Constructor
     *
     * @param \Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory
     * @param \Magento\InventorySourceDeductionApi\Model\ItemToDeductInterfaceFactory $itemToDeductFactory
     * @param \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
     * @return void
     */
    public function __construct(
        \Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory,
        \Magento\InventorySourceDeductionApi\Model\ItemToDeductInterfaceFactory $itemToDeductFactory,
        \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
    )
    {
        $this->sourceDeductionRequestFactory = $sourceDeductionRequestFactory;
        $this->itemToDeductFactory = $itemToDeductFactory;
        $this->storeSalesChannelFactory = $storeSalesChannelFactory;
    }

    /**
     * Get items
     *
     * @param \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterface[] $sourceSelectionItems
     * @return \Magento\InventorySourceDeductionApi\Model\ItemToDeductInterface[]
     */
    protected function getItems(array $sourceSelectionItems)
    {
        $items = [];
        foreach ($sourceSelectionItems as $sourceSelectionItem) {
            if ($sourceSelectionItem->getQtyToDeduct() < 0.000001) {
                continue;
            }
            $sourceCode = $sourceSelectionItem->getSourceCode();
            if (!isset($items[$sourceCode])) {
                $items[$sourceCode] = [];
            }
            $items[$sourceCode][] = $this->itemToDeductFactory->create([
                'sku' => $sourceSelectionItem->getSku(),
                'qty' => $sourceSelectionItem->getQtyToDeduct(),
            ]);
        }
        return $items;
    }

    /**
     * Around create
     *
     * @param \Magento\InventoryShipping\Model\SourceDeductionRequestsFromSourceSelectionFactory $subject
     * @param \Closure $proceed
     * @param \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface $sourceSelectionResult
     * @param \Magento\InventorySalesApi\Api\Data\SalesEventInterface $salesEvent
     * @param int $storeId
     * @return array
     */
    public function aroundCreate(
        \Magento\InventoryShipping\Model\SourceDeductionRequestsFromSourceSelectionFactory $subject,
        \Closure $proceed,
        \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface $sourceSelectionResult,
        \Magento\InventorySalesApi\Api\Data\SalesEventInterface $salesEvent,
        int $storeId
    ): array
    {
        $sourceDeductionRequests = [];
        foreach ($this->getItems($sourceSelectionResult->getSourceSelectionItems()) as $sourceCode => $sourceItems) {
            $sourceDeductionRequests[] = $this->sourceDeductionRequestFactory->create([
                'sourceCode' => $sourceCode,
                'items' => $sourceItems,
                'salesChannel' => $this->storeSalesChannelFactory->createByStore($storeId),
                'salesEvent' => $salesEvent,
            ]);
        }
        return $sourceDeductionRequests;
    }

}
