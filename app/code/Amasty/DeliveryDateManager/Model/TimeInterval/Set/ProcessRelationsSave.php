<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval\Set;

use Amasty\DeliveryDateManager\Model\Relations\TimeIntervalChannelRelation\SaveSetOfChannelTime;
use Amasty\DeliveryDateManager\Model\Relations\TimeIntervalDateScheduleRelation\SaveSetOfDateTime;

/**
 * Process relations of Time Interval Set to Time Interval.
 * Establish relation links.
 */
class ProcessRelationsSave
{
    /**
     * @var SaveSetOfDateTime
     */
    private $saveSetOfDateTime;

    /**
     * @var SaveSetOfChannelTime
     */
    private $saveSetOfChannelTime;

    public function __construct(SaveSetOfDateTime $saveSetOfDateTime, SaveSetOfChannelTime $saveSetOfChannelTime)
    {
        $this->saveSetOfDateTime = $saveSetOfDateTime;
        $this->saveSetOfChannelTime = $saveSetOfChannelTime;
    }

    /**
     * @param DataModel $timeSet
     */
    public function processSave(DataModel $timeSet): void
    {
        $this->saveSetOfDateTime->save($timeSet->getScheduleIds(), $timeSet->getTimeIds());
        $this->saveSetOfChannelTime->save($timeSet->getChannelIds(), $timeSet->getTimeIds());
    }
}
