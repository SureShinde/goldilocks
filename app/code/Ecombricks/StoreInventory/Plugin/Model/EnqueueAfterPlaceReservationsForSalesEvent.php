<?php

namespace Ecombricks\StoreInventory\Plugin\Model;

use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\InventoryIndexer\Model\Queue\ReservationData;
use Magento\InventoryIndexer\Model\Queue\ReservationDataFactory;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Model\GetAssignedStockIdForWebsiteInterface;
use Magento\InventorySales\Model\PlaceReservationsForSalesEvent;

/**
 * Enqueue reservations processing after appending in order to recalculate index salability status.
 */
class EnqueueAfterPlaceReservationsForSalesEvent
{
    /**
     * Queue topic name.
     */
    private const TOPIC_RESERVATIONS_UPDATE_SALABILITY_STATUS = 'inventory.reservations.updateSalabilityStatus';

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var GetAssignedStockIdForWebsiteInterface
     */
    private $getAssignedStockIdForWebsite;

    /**
     * @var ReservationDataFactory
     */
    private $reservationDataFactory;
    /**
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    private $stockIdByStore;

    /**
     * @param PublisherInterface $publisher
     * @param GetAssignedStockIdForWebsiteInterface $getAssignedStockIdForWebsite
     * @param ReservationDataFactory $reservationDataFactory
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $stockIdByStore
     */
    public function __construct(
        PublisherInterface                    $publisher,
        GetAssignedStockIdForWebsiteInterface $getAssignedStockIdForWebsite,
        ReservationDataFactory                $reservationDataFactory,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $stockIdByStore
    ) {
        $this->publisher = $publisher;
        $this->getAssignedStockIdForWebsite = $getAssignedStockIdForWebsite;
        $this->reservationDataFactory = $reservationDataFactory;
        $this->stockIdByStore = $stockIdByStore;
    }

    /**
     * Publish reservation data for reindex.
     *
     * @param PlaceReservationsForSalesEvent $subject
     * @param void $result
     * @param ItemToSellInterface[] $items
     * @param SalesChannelInterface $salesChannel
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        PlaceReservationsForSalesEvent $subject,
        $result,
        array                                      $items,
        SalesChannelInterface                      $salesChannel
    ): void {
        $this->publisher->publish(
            self::TOPIC_RESERVATIONS_UPDATE_SALABILITY_STATUS,
            $this->getReservationsDataObject($salesChannel, $items)
        );
    }

    /**
     * Build reservation data transfer objects.
     *
     * @param SalesChannelInterface $salesChannel
     * @param ItemToSellInterface[] $items
     *
     * @return ReservationData
     */
    private function getReservationsDataObject(SalesChannelInterface $salesChannel, array $items): ReservationData
    {
        $stockId = $this->stockIdByStore->execute();
        $skus = array_map(
            function (ItemToSellInterface $itemToSell): string {
                return $itemToSell->getSku();
            },
            $items
        );

        return $this->reservationDataFactory->create(
            [
                'stock' => $stockId,
                'skus' => $skus
            ]
        );
    }
}
