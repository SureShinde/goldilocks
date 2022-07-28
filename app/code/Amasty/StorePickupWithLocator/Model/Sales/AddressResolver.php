<?php

namespace Amasty\StorePickupWithLocator\Model\Sales;

use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\Storelocator\Model\ResourceModel\Location as LocationResource;
use Magento\Directory\Model\ResourceModel\Region\Collection;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class AddressResolver
{
    /**
     * @var LocationFactory
     */
    private $locationFactory;

    /**
     * @var LocationResource
     */
    private $locationResource;

    /**
     * @var OrderRepositoryInterface
     */
    private $magentoOrderRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Collection
     */
    private $regionCollection;

    public function __construct(
        Collection $regionCollection,
        LocationFactory $locationFactory,
        LocationResource $locationResource,
        OrderRepositoryInterface $magentoOrderRepository,
        CartRepositoryInterface $quoteRepository,
        ConfigProvider $configProvider
    ) {
        $this->regionCollection = $regionCollection;
        $this->locationFactory = $locationFactory;
        $this->locationResource = $locationResource;
        $this->magentoOrderRepository = $magentoOrderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->configProvider = $configProvider;
    }

    /**
     * @param Order|CartInterface $entity
     * @param int $locationId
     */
    public function setShippingInformation($entity, $locationId)
    {
        /** @var \Amasty\Storelocator\Model\Location $location */
        $location = $this->locationFactory->create();
        $this->locationResource->load($location, $locationId);

        $carrierTitle = $this->configProvider->getCarrierTitle() ?: 'Store Pickup';
        $region = is_numeric($location->getState())
            ? $this->getRegionNameById($location->getState())
            : $location->getState();
        $regionId = is_numeric($location->getState()) ? $location->getState() : null;

        $entity->getShippingAddress()
            ->setFirstname(__($carrierTitle . ':'))
            ->setLastname($location->getName())
            ->setCountryId($location->getCountry())
            ->setRegion($region)
            ->setRegionId($regionId)
            ->setStreet($location->getAddress())
            ->setCity($location->getCity())
            ->setPostcode($location->getZip())
            ->setTelephone($location->getPhone());

        if ($entity instanceof Order) {
            $entity->setShippingDescription((string)__($carrierTitle . ' - ' . $location->getName()));
            $this->magentoOrderRepository->save($entity);
        } elseif ($entity instanceof CartInterface) {
            $this->quoteRepository->save($entity);
        }
    }

    /**
     * @param int $id
     * @return string
     */
    private function getRegionNameById($id)
    {
        $region = $this->regionCollection->getItemById($id);

        return $region->getDefaultName();
    }
}
