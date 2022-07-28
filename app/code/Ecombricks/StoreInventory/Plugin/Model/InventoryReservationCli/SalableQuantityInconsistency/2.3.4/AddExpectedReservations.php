<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventoryReservationCli\SalableQuantityInconsistency;

if (version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.4', '>=')) :

/**
 * Add expected reservations plugin
 */
class AddExpectedReservations
{

    /**
     * Reservation builder
     *
     * @var \Magento\InventoryReservationsApi\Model\ReservationBuilderInterface
     */
    protected $reservationBuilder;

    /**
     * Get order items data for order in not final state
     *
     * @var \Magento\InventoryReservationCli\Model\ResourceModel\GetOrderItemsDataForOrdersInNotFinalState
     */
    protected $getOrderItemsDataForOrderInNotFinalState;

    /**
     * Serializer
     *
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Constructor
     *
     * @param \Magento\InventoryReservationsApi\Model\ReservationBuilderInterface $reservationBuilder
     * @param \Magento\InventoryReservationCli\Model\ResourceModel\GetOrderItemsDataForOrdersInNotFinalState $getOrderItemsDataForOrderInNotFinalState
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     */
    public function __construct(
        \Magento\InventoryReservationsApi\Model\ReservationBuilderInterface $reservationBuilder,
        \Magento\InventoryReservationCli\Model\ResourceModel\GetOrderItemsDataForOrdersInNotFinalState $getOrderItemsDataForOrderInNotFinalState,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
    )
    {
        $this->reservationBuilder = $reservationBuilder;
        $this->getOrderItemsDataForOrderInNotFinalState = $getOrderItemsDataForOrderInNotFinalState;
        $this->serializer = $serializer;
        $this->getStockIdByStore = $getStockIdByStore;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\AddExpectedReservations $subject
     * @param \Closure $proceed
     * @param Collector $collector
     * @param int $bunchSize
     * @param int $page
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function aroundExecute(
        \Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\AddExpectedReservations $subject,
        \Closure $proceed,
        Collector $collector,
        int $bunchSize = 50,
        int $page = 1
    )
    {
        $this->setSubject($subject);
        foreach ($this->getOrderItemsDataForOrderInNotFinalState->execute($bunchSize, $page) as $data) {
            $stockId = $this->getStockIdByStore->execute((int) $data['store_id']);
            $reservation = $this->reservationBuilder
                ->setSku($data['sku'])
                ->setQuantity((float)$data['qty_ordered'])
                ->setStockId($stockId)
                ->setMetadata($this->serializer->serialize(['object_id' => (int)$data['entity_id']]))
                ->build();

            $collector->addReservation($reservation);
            $collector->addOrderData($data);
        }
    }

}

endif;
