<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval\Set;

use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set;
use Amasty\DeliveryDateManager\Model\TimeInterval\Set\DataModelFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Get
{
    /**
     * @var DataModelFactory
     */
    private $timeSetFactory;

    /**
     * @var Set
     */
    private $timeSetResource;

    public function __construct(DataModelFactory $timeSetFactory, Set $timeSetResource)
    {
        $this->timeSetFactory = $timeSetFactory;
        $this->timeSetResource = $timeSetResource;
    }

    /**
     * @param int|null $setId
     * @return DataModel
     * @throws NoSuchEntityException
     */
    public function execute($setId = null): DataModel
    {
        $timeSet = $this->timeSetFactory->create();

        if ($setId) {
            $this->timeSetResource->load($timeSet, $setId);

            if ($setId !== $timeSet->getId()) {
                throw new NoSuchEntityException(
                    __('Time Interval Set with ID "%1" does not exist.', $setId)
                );
            }
        }

        return $timeSet;
    }
}
