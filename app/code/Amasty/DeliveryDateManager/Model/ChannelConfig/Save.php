<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ChannelConfig;

use Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig as ChannelConfigResource;
use Magento\Framework\Exception\CouldNotSaveException;

class Save
{
    /**
     * @var ChannelConfigResource
     */
    private $resourceModel;

    public function __construct(
        ChannelConfigResource $orderLimitResource
    ) {
        $this->resourceModel = $orderLimitResource;
    }

    /**
     * @param ChannelConfigDataInterface|ConfigData $modelData
     *
     * @return ChannelConfigDataInterface
     * @throws CouldNotSaveException
     */
    public function execute(ChannelConfigDataInterface $modelData): ChannelConfigDataInterface
    {
        try {
            $this->resourceModel->save($modelData);
        } catch (\Exception $e) {
            if ($modelData->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save Channel Configuration with ID %1. Error: %2',
                        [$modelData->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save Channel Configuration. Error: %1', $e->getMessage()));
        }

        return $modelData;
    }
}
