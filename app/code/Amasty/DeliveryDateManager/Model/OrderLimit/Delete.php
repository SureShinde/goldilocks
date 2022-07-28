<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\OrderLimit;

use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit as OrderLimitResource;
use Magento\Framework\Exception\CouldNotDeleteException;

class Delete
{
    /**
     * @var OrderLimitResource
     */
    private $resourceModel;

    public function __construct(OrderLimitResource $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param LimitDataModel $limitDataModel
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function execute(LimitDataModel $limitDataModel): bool
    {
        try {
            $this->resourceModel->delete($limitDataModel);
        } catch (\Exception $e) {
            if ($limitDataModel->getLimitId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove order limit with ID %1. Error: %2',
                        [$limitDataModel->getLimitId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove order limit. Error: %1', $e->getMessage()));
        }

        return true;
    }
}
