<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ChannelConfig;

use Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface;
use Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig as ChannelConfigResource;
use Magento\Framework\Exception\NoSuchEntityException;

class Get
{
    /**
     * @var ChannelConfigResource
     */
    private $resourceModel;

    /**
     * @var ChannelConfigDataInterfaceFactory
     */
    private $modelFactory;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        ChannelConfigResource $resourceModel,
        ChannelConfigDataInterfaceFactory $modelFactory,
        Registry $registry
    ) {
        $this->resourceModel = $resourceModel;
        $this->modelFactory = $modelFactory;
        $this->registry = $registry;
    }

    /**
     * @param int $itemId
     *
     * @return ChannelConfigDataInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $itemId): ChannelConfigDataInterface
    {
        if (!$this->registry->isset($itemId)) {
            /** @var ChannelConfigDataInterface $modelData */
            $modelData = $this->modelFactory->create();
            $this->resourceModel->load($modelData, $itemId);

            if ($itemId !== $modelData->getId()) {
                throw new NoSuchEntityException(
                    __('Channel Configuration with ID "%value" does not exist.', ['value' => $itemId])
                );
            }

            $this->registry->set($itemId, $modelData);
        }

        return $this->registry->get($itemId);
    }
}
