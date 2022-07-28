<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\OrderLimit;

use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit as OrderLimitResource;
use Magento\Framework\Exception\NoSuchEntityException;

class Get
{
    /**
     * @var OrderLimitResource
     */
    private $resourceModel;

    /**
     * @var OrderLimitInterfaceFactory
     */
    private $modelFactory;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        OrderLimitResource $resourceModel,
        OrderLimitInterfaceFactory $modelFactory,
        Registry $registry
    ) {
        $this->resourceModel = $resourceModel;
        $this->modelFactory = $modelFactory;
        $this->registry = $registry;
    }

    /**
     * @param int $itemId
     *
     * @return OrderLimitInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $itemId): OrderLimitInterface
    {
        if (!$this->registry->isset($itemId)) {
            /** @var OrderLimitInterface $modelData */
            $modelData = $this->modelFactory->create();
            $this->resourceModel->load($modelData, $itemId);

            if ($itemId !== $modelData->getLimitId()) {
                throw new NoSuchEntityException(
                    __('Order Limit with ID "%value" does not exist.', ['value' => $itemId])
                );
            }

            $this->registry->set($itemId, $modelData);
        }

        return $this->registry->get($itemId);
    }
}
