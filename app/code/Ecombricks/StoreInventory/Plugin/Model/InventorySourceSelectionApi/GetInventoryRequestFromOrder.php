<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventorySourceSelectionApi;

/**
 * Get inventory request from order plugin
 */
class GetInventoryRequestFromOrder extends \Ecombricks\Common\Plugin\Plugin
{

    /**
     * Inventory request factory
     *
     * @var \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory
     */
    protected $inventoryRequestFactory;

    /**
     * Order repository
     *
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

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
     * @param \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory $inventoryRequestFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
    )
    {
        parent::__construct($wrapperFactory);
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->orderRepository = $orderRepository;
        $this->getStockIdByStore = $getStockIdByStore;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventorySourceSelectionApi\Model\GetInventoryRequestFromOrder $subject
     * @param \Closure $proceed
     * @param int $orderId
     * @param array $requestItems
     * @return \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface
     */
    public function aroundExecute(
        \Magento\InventorySourceSelectionApi\Model\GetInventoryRequestFromOrder $subject,
        \Closure $proceed,
        int $orderId,
        array $requestItems
    ): \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface
    {
        $this->setSubject($subject);
        $order = $this->orderRepository->get($orderId);
        $inventoryRequest = $this->inventoryRequestFactory->create([
            'stockId' => $this->getStockIdByStore->execute($order->getStoreId()),
            'items' => $requestItems,
        ]);
        $address = $this->invokeSubjectMethod('getAddressFromOrder', $order);
        if ($address !== null) {
            $inventoryRequest->getExtensionAttributes()->setDestinationAddress($address);
        }
        return $inventoryRequest;
    }

}
