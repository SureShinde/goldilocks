<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ChannelConfig;

use Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig as ChannelConfigResource;
use Magento\Framework\Exception\CouldNotDeleteException;

class Delete
{
    /**
     * @var ChannelConfigResource
     */
    private $resourceModel;

    public function __construct(ChannelConfigResource $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param ConfigData $configDataModel
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function execute(ConfigData $configDataModel): bool
    {
        try {
            $this->resourceModel->delete($configDataModel);
        } catch (\Exception $e) {
            if ($configDataModel->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove channel configuration with ID %1. Error: %2',
                        [$configDataModel->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove channel configuration. Error:%1', $e->getMessage()));
        }

        return true;
    }
}
