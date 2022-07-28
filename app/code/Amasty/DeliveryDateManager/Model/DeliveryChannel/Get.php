<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannel;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel as DeliveryChannelResource;
use Magento\Framework\Exception\NoSuchEntityException;

class Get
{
    /**
     * @var DeliveryChannelResource
     */
    private $resourceModel;

    /**
     * @var DeliveryChannelInterfaceFactory
     */
    private $modelFactory;

    /**
     * @var array
     */
    private $storage = [];

    public function __construct(
        DeliveryChannelInterfaceFactory $modelFactory,
        DeliveryChannelResource $resourceModel
    ) {
        $this->modelFactory = $modelFactory;
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param Int $itemId
     * @return DeliveryChannelInterface
     */
    public function execute(Int $itemId): DeliveryChannelInterface
    {
        if (!isset($this->storage[$itemId])) {
            /** @var DeliveryChannelInterface $modelData */
            $modelData = $this->modelFactory->create();
            $this->resourceModel->load($modelData, $itemId);

            if ($itemId !== $modelData->getChannelId()) {
                throw new NoSuchEntityException(
                    __('Channel with ID "%value" does not exist.', ['value' => $itemId])
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
