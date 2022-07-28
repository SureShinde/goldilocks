<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DateSchedule;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule as DateScheduleResource;
use Magento\Framework\Exception\NoSuchEntityException;

class Get
{
    /**
     * @var DateScheduleResource
     */
    private $resourceModel;

    /**
     * @var DateScheduleInterfaceFactory
     */
    private $modelFactory;

    /**
     * @var array
     */
    private $storage = [];

    public function __construct(
        DateScheduleResource $resourceModel,
        DateScheduleInterfaceFactory $modelFactory
    ) {
        $this->resourceModel = $resourceModel;
        $this->modelFactory = $modelFactory;
    }

    /**
     * @param int $itemId
     *
     * @return DateScheduleInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $itemId): DateScheduleInterface
    {
        if (!isset($this->storage[$itemId])) {
            /** @var DateScheduleInterface $modelData */
            $modelData = $this->modelFactory->create();
            $this->resourceModel->load($modelData, $itemId);

            if ($itemId !== $modelData->getScheduleId()) {
                throw new NoSuchEntityException(
                    __('Date Schedule with ID "%value" does not exist.', ['value' => $itemId])
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
