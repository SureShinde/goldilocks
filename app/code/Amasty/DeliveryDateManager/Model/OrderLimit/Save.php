<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\OrderLimit;

use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit as OrderLimitResource;
use Magento\Framework\Exception\CouldNotSaveException;

class Save
{
    /**
     * @var OrderLimitResource
     */
    private $orderLimitResource;

    public function __construct(
        OrderLimitResource $orderLimitResource
    ) {
        $this->orderLimitResource = $orderLimitResource;
    }

    /**
     * @param LimitDataModel $orderLimitModel
     *
     * @return OrderLimitInterface
     * @throws CouldNotSaveException
     */
    public function execute(LimitDataModel $orderLimitModel) :OrderLimitInterface
    {
        try {
            $this->orderLimitResource->save($orderLimitModel);
        } catch (\Exception $e) {
            if ($orderLimitModel->getLimitId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save order limit with ID %1. Error: %2',
                        [$orderLimitModel->getLimitId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new order limit. Error: %1', $e->getMessage()));
        }

        return $orderLimitModel;
    }
}
