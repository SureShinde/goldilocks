<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval\Set;

use Amasty\DeliveryDateManager\Model\ModalDuplicateResolver\ResolverInterface;
use Amasty\DeliveryDateManager\Model\TimeInterval\Duplicate as TimeIntervalDuplicator;

class Duplicate implements ResolverInterface
{
    /**
     * @var Get
     */
    private $timeSetGetter;

    /**
     * @var Save
     */
    private $timeSetSaver;

    /**
     * @var TimeIntervalDuplicator
     */
    private $timeDuplicator;

    public function __construct(
        Get $timeSetGetter,
        Save $timeSetSaver,
        TimeIntervalDuplicator $timeDuplicator
    ) {
        $this->timeSetGetter = $timeSetGetter;
        $this->timeSetSaver = $timeSetSaver;
        $this->timeDuplicator = $timeDuplicator;
    }

    /**
     * @param int $timeSetId
     * @return int
     */
    public function execute(int $timeSetId): int
    {
        /** @var DataModel $mainTimeSet */
        $mainTimeSet = $this->timeSetGetter->execute($timeSetId);

        /** @var DataModel $newInterval */
        $newTimeSet = $this->timeSetGetter->execute(null);
        $newTimeIds = $this->getNewTimeIds($mainTimeSet->getTimeIds());
        $newTimeSet->setTimeIds($newTimeIds);
        $newTimeSet->setName('Copy of ' . $mainTimeSet->getName());
        $newTimeSet = $this->timeSetSaver->execute($newTimeSet);

        return $newTimeSet->getId();
    }

    /**
     * @param array $timeIds
     * @return array
     */
    private function getNewTimeIds(array $timeIds): array
    {
        $newTimeIds = [];
        foreach ($timeIds as $timeId) {
            $newTimeIds[] = $this->timeDuplicator->execute((int)$timeId);
        }

        return $newTimeIds;
    }
}
