<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateOrder as DeliveryDateOrderResource;
use Magento\Framework\Exception\CouldNotSaveException;

class Save
{
    /**
     * @var DeliveryDateOrderResource
     */
    private $resource;

    public function __construct(
        DeliveryDateOrderResource $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @param DeliveryDateOrderInterface $modelData
     *
     * @return DeliveryDateOrderInterface
     * @throws CouldNotSaveException
     */
    public function execute(DeliveryDateOrderInterface $modelData): DeliveryDateOrderInterface
    {
        try {
            $this->resource->save($modelData);
        } catch (\Exception $e) {
            if ($modelData->getDeliverydateId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save Delivery Date order information with ID %1. Error: %2',
                        [$modelData->getDeliverydateId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new delivery order. Error: %1', $e->getMessage()));
        }

        return $modelData;
    }
}
