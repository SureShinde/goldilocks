<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventoryShipping;

/**
 * Source deduction request from shipment factory plugin
 */
class SourceDeductionRequestFromShipmentFactory
{

    /**
     * Source deduction request factory
     *
     * @var \Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterfaceFactory
     */
    protected $sourceDeductionRequestFactory;

    /**
     * Store sales channel factory
     *
     * @var \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory
     */
    protected $storeSalesChannelFactory;

    /**
     * Sales event factory
     *
     * @var \Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory
     */
    protected $salesEventFactory;

    /**
     * Constructor
     *
     * @param \Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory
     * @param \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
     * @param \Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory $salesEventFactory
     * @return void
     */
    public function __construct(
        \Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory,
        \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory,
        \Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory $salesEventFactory
    )
    {
        $this->sourceDeductionRequestFactory = $sourceDeductionRequestFactory;
        $this->storeSalesChannelFactory = $storeSalesChannelFactory;
        $this->salesEventFactory = $salesEventFactory;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventoryShipping\Model\SourceDeductionRequestFromShipmentFactory $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param string $sourceCode
     * @param array $items
     * @return \Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterface
     */
    public function aroundExecute(
        \Magento\InventoryShipping\Model\SourceDeductionRequestFromShipmentFactory $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order\Shipment $shipment,
        string $sourceCode,
        array $items
    ): \Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterface
    {
        return $this->sourceDeductionRequestFactory->create([
            'sourceCode' => $sourceCode,
            'items' => $items,
            'salesChannel' => $this->storeSalesChannelFactory->createByStore($shipment->getOrder()->getStore()),
            'salesEvent' => $this->salesEventFactory->create([
                'type' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::EVENT_SHIPMENT_CREATED,
                'objectType' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::OBJECT_TYPE_ORDER,
                'objectId' => $shipment->getOrderId(),
            ]),
        ]);
    }

}
