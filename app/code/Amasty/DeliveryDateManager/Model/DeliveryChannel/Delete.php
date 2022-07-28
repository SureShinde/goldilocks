<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannel;

use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel as DeliveryChannelResource;
use Magento\Framework\Exception\CouldNotDeleteException;

class Delete
{
    /**
     * @var DeliveryChannelResource
     */
    private $resourceModel;

    public function __construct(DeliveryChannelResource $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param DeliveryChannelData $channelDataModel
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function execute(DeliveryChannelData $channelDataModel): bool
    {
        try {
            $this->resourceModel->delete($channelDataModel);
        } catch (\Exception $e) {
            if ($channelDataModel->getChannelId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove channel with ID %1. Error: %2',
                        [$channelDataModel->getChannelId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove channel. Error: %1', $e->getMessage()));
        }

        return true;
    }
}
