<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryQuote;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateQuoteInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryDateQuoteInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateQuote as DeliveryQuoteResource;
use Magento\Framework\Exception\NoSuchEntityException;

class Get
{
    /**
     * @var DeliveryQuoteResource
     */
    private $resourceModel;

    /**
     * @var DeliveryDateQuoteInterfaceFactory
     */
    private $modelFactory;

    /**
     * @var DeliveryDateQuoteInterface[]
     */
    private $storage = [];

    /**
     * @var DeliveryDateQuoteInterface[]
     */
    private $storageByAddress = [];

    public function __construct(
        DeliveryQuoteResource $resourceModel,
        DeliveryDateQuoteInterfaceFactory $modelFactory
    ) {
        $this->resourceModel = $resourceModel;
        $this->modelFactory = $modelFactory;
    }

    /**
     * @param int $itemId
     * @return DeliveryDateQuoteInterface
     */
    public function execute(int $itemId): DeliveryDateQuoteInterface
    {
        if (!isset($this->storage[$itemId])) {
            /** @var DeliveryDateQuoteInterface $modelData */
            $modelData = $this->modelFactory->create();
            $this->resourceModel->load($modelData, $itemId);

            if ($itemId !== $modelData->getDeliveryQuoteId()) {
                throw new NoSuchEntityException(
                    __('Delivery Quote with ID "%value" does not exist.', ['value' => $itemId])
                );
            }

            $this->storage[$itemId] = $modelData;
        }

        return $this->storage[$itemId];
    }

    /**
     * @param int $addressId
     *
     * @return DeliveryDateQuoteInterface
     */
    public function getByAddressId(int $addressId): DeliveryDateQuoteInterface
    {
        if (!isset($this->storageByAddress[$addressId])) {
            /** @var DeliveryDateQuoteInterface $modelData */
            $modelData = $this->modelFactory->create();
            $this->resourceModel->load($modelData, $addressId, DeliveryDateQuoteInterface::QUOTE_ADDRESS_ID);
            $this->storageByAddress[$addressId] = $modelData;
        }

        return $this->storageByAddress[$addressId];
    }

    /**
     * @param int|null $itemId
     */
    public function clearStorage(?int $itemId): void
    {
        if ($itemId !== null) {
            unset($this->storage[$itemId], $this->storageByAddress[$itemId]);
        } else {
            $this->storage = [];
            $this->storageByAddress = [];
        }
    }
}
