<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannel;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel as DeliveryChannelResource;
use Magento\Framework\Exception\CouldNotSaveException;

class Save
{
    /**
     * @var DeliveryChannelResource
     */
    private $deliveryChannelResource;

    public function __construct(
        DeliveryChannelResource $deliveryChannelResource
    ) {
        $this->deliveryChannelResource = $deliveryChannelResource;
    }

    /**
     * @param DeliveryChannelData $deliveryChannelModel
     *
     * @return DeliveryChannelInterface
     * @throws CouldNotSaveException
     */
    public function execute(DeliveryChannelData $deliveryChannelModel) :DeliveryChannelInterface
    {
        try {
            $this->deliveryChannelResource->save($deliveryChannelModel);
        } catch (\Exception $e) {
            if ($deliveryChannelModel->getChannelId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save channel with ID %1. Error: %2',
                        [$deliveryChannelModel->getChannelId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new channel. Error: %1', $e->getMessage()));
        }

        return $deliveryChannelModel;
    }
}
