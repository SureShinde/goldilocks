<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval as TimeIntervalInterfaceResource;
use Magento\Framework\Exception\NoSuchEntityException;

class Get
{
    /**
     * @var TimeIntervalInterfaceResource
     */
    private $resourceModel;

    /**
     * @var
     */
    private $modelFactory;

    /**
     * @var array
     */
    private $storage = [];

    public function __construct(
        TimeIntervalInterfaceFactory $modelFactory,
        TimeIntervalInterfaceResource $resourceModel
    ) {
        $this->modelFactory = $modelFactory;
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param Int $itemId
     * @return TimeIntervalInterface
     */
    public function execute(Int $itemId): TimeIntervalInterface
    {
        if (!isset($this->storage[$itemId])) {
            /** @var TimeIntervalInterface $modelData */
            $modelData = $this->modelFactory->create();
            $this->resourceModel->load($modelData, $itemId);

            if ($itemId !== $modelData->getIntervalId()) {
                throw new NoSuchEntityException(
                    __('Time Interval with ID "%1" does not exist.', $itemId)
                );
            }

            $this->storage[$itemId] = $modelData;
        }

        return $this->storage[$itemId];
    }

    /**
     * @param int|null $itemId
     */
    public function clearStorage(?int $itemId): void
    {
        if ($itemId !== null) {
            unset($this->storage[$itemId]);
        } else {
            $this->storage = [];
        }
    }
}
