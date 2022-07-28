<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventoryReservationCli\SalableQuantityInconsistency;

if (
    version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.2', '>=') &&
    !version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.4', '>=')
) :

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
     * Get orders in not final state
     *
     * @var \Magento\InventoryReservationCli\Model\GetOrdersInNotFinalState
     */
    protected $getOrdersInNotFinalState;

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
     * @param \Magento\InventoryReservationCli\Model\GetOrdersInNotFinalState $getOrdersInNotFinalState
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     */
    public function __construct(
        \Magento\InventoryReservationsApi\Model\ReservationBuilderInterface $reservationBuilder,
        \Magento\InventoryReservationCli\Model\GetOrdersInNotFinalState $getOrdersInNotFinalState,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
    )
    {
        $this->reservationBuilder = $reservationBuilder;
        $this->getOrdersInNotFinalState = $getOrdersInNotFinalState;
        $this->serializer = $serializer;
        $this->getStockIdByStore = $getStockIdByStore;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\AddExpectedReservations $subject
     * @param \Closure $proceed
     * @param \Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\Collector $collector
     * @return void
     */
    public function aroundExecute(
        \Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\AddExpectedReservations $subject,
        \Closure $proceed,
        \Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\Collector $collector
    ): void
    {
        foreach ($this->getOrdersInNotFinalState->execute() as $order) {
            $stockId = $this->getStockIdByStore->execute($order->getStore());
            foreach ($order->getItems() as $item) {
                if ($item->getHasChildren()) {
                    continue;
                }
                $reservation = $this->reservationBuilder
                    ->setSku($item->getSku())
                    ->setQuantity((float) $item->getQtyOrdered())
                    ->setStockId($stockId)
                    ->setMetadata($this->serializer->serialize(['object_id' => (int) $order->getEntityId()]))
                    ->build();
                $collector->addReservation($reservation);
                $collector->addOrder($order);
            }
        }
    }

}

endif;
