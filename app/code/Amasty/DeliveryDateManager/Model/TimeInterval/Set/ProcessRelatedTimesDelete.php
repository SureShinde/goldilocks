<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval\Set;

use Amasty\DeliveryDateManager\Model\OrderLimit\Delete as OrderLimitDelete;
use Amasty\DeliveryDateManager\Model\OrderLimit\Get as OrderLimitGet;
use Amasty\DeliveryDateManager\Model\TimeInterval\Delete as DeleteTimeInterval;
use Amasty\DeliveryDateManager\Model\TimeInterval\Provider;

/**
 * Process delete related times of time interval set.
 */
class ProcessRelatedTimesDelete
{
    /**
     * @var DeleteTimeInterval
     */
    private $timeIntervalDeleter;

    /**
     * @var OrderLimitGet
     */
    private $orderLimitGetter;

    /**
     * @var OrderLimitDelete
     */
    private $orderLimitDeleter;

    /**
     * @var Provider
     */
    private $timesProvider;

    public function __construct(
        OrderLimitGet $orderLimitGetter,
        OrderLimitDelete $orderLimitDeleter,
        DeleteTimeInterval $timeIntervalDeleter,
        Provider $timesProvider
    ) {
        $this->orderLimitGetter = $orderLimitGetter;
        $this->orderLimitDeleter = $orderLimitDeleter;
        $this->timeIntervalDeleter = $timeIntervalDeleter;
        $this->timesProvider = $timesProvider;
    }

    /**
     * @param array $setIntervalIds
     * @param array $existedTimeIds
     */
    public function processDelete(array $setIntervalIds, array $existedTimeIds): void
    {
        $idsToDelete = array_diff($existedTimeIds, $setIntervalIds);
        $timesList = $this->timesProvider->getListByIds($idsToDelete);

        foreach ($timesList->getItems() as $timeInterval) {
            if ($limitId = $timeInterval->getLimitId()) {
                $this->deleteRelatedLimits($limitId);
            }
            $this->timeIntervalDeleter->execute($timeInterval);
        }
    }

    /**
     * @param int $limitId
     */
    private function deleteRelatedLimits(int $limitId): void
    {
        $limit = $this->orderLimitGetter->execute($limitId);
        $this->orderLimitDeleter->execute($limit);
    }
}
