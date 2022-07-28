<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval;

use Amasty\DeliveryDateManager\Model\ModalDuplicateResolver\ResolverInterface;

class Duplicate implements ResolverInterface
{
    /**
     * @var Get
     */
    private $timesGetter;

    /**
     * @var Save
     */
    private $timesSaver;

    /**
     * @var TimeIntervalDataModelFactory
     */
    private $timesFactory;

    public function __construct(
        Get $timesGetter,
        Save $timesSaver,
        TimeIntervalDataModelFactory $timesFactory
    ) {
        $this->timesGetter = $timesGetter;
        $this->timesSaver = $timesSaver;
        $this->timesFactory = $timesFactory;
    }

    /**
     * @param int $intervalId
     * @return int
     */
    public function execute(int $intervalId): int
    {
        /** @var TimeIntervalDataModel $mainInterval */
        $mainInterval = $this->timesGetter->execute($intervalId);

        /** @var TimeIntervalDataModel $newInterval */
        $newInterval = $this->timesFactory->create();
        $newInterval->setData($mainInterval->getData());
        $newInterval->setIntervalId(null);
        $newInterval = $this->timesSaver->execute($newInterval);

        return $newInterval->getIntervalId();
    }
}
