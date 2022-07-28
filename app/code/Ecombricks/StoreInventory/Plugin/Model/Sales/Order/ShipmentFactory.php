<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\Sales\Order;

/**
 * Order shipment factory plugin
 */
class ShipmentFactory
{

    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Shipment extension factory
     *
     * @var \Magento\Sales\Api\Data\ShipmentExtensionFactory
     */
    protected $shipmentExtensionFactory;

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Get sources assigned to stock ordered by priority
     *
     * @var \Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface
     */
    protected $getSourcesAssignedToStockOrderedByPriority;

    /**
     * Default source provider
     *
     * @var \Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface
     */
    protected $defaultSourceProvider;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Api\Data\ShipmentExtensionFactory $shipmentExtensionFactory
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @param \Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
     * @param \Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface $defaultSourceProvider
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Api\Data\ShipmentExtensionFactory $shipmentExtensionFactory,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore,
        \Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority,
        \Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface $defaultSourceProvider
    )
    {
        $this->request = $request;
        $this->shipmentExtensionFactory = $shipmentExtensionFactory;
        $this->getStockIdByStore = $getStockIdByStore;
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * After create
     *
     * @param \Magento\Sales\Model\Order\ShipmentFactory $subject
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterCreate(
        \Magento\Sales\Model\Order\ShipmentFactory $subject,
        \Magento\Sales\Api\Data\ShipmentInterface $shipment,
        \Magento\Sales\Model\Order $order
    )
    {
        $sourceCode = $this->request->getParam('sourceCode');
        if (empty($sourceCode)) {
            $stockId = $this->getStockIdByStore->execute($order->getStore());
            $sources = $this->getSourcesAssignedToStockOrderedByPriority->execute($stockId);
            if (!empty($sources) && count($sources) == 1) {
                $sourceCode = $sources[0]->getSourceCode();
            } else {
                $sourceCode = $this->defaultSourceProvider->getCode();
            }
        }
        $shipmentExtension = $shipment->getExtensionAttributes();
        if (empty($shipmentExtension)) {
            $shipmentExtension = $this->shipmentExtensionFactory->create();
        }
        $shipmentExtension->setSourceCode($sourceCode);
        $shipment->setExtensionAttributes($shipmentExtension);
        return $shipment;
    }

}
