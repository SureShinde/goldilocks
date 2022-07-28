<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\DataPreprocessor;

use Amasty\DeliveryDateManager\Model\Preprocessor\PreprocessorInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set as TimeIntervalSetResource;
use Amasty\DeliveryDateManager\Model\TimeInterval\Set\DataModel;
use Amasty\DeliveryDateManager\Model\TimeInterval\Set\Get;

class TimeIntervalDateScheduleRelation implements PreprocessorInterface
{
    public const TIME_SETS_KEY = 'prepared_time_sets_for_save';

    /**
     * @var Get
     */
    private $timeSetGetter;

    /**
     * @var TimeIntervalSetResource
     */
    private $timeSetResource;

    public function __construct(Get $timeSetGetter, TimeIntervalSetResource $timeSetResource)
    {
        $this->timeSetGetter = $timeSetGetter;
        $this->timeSetResource = $timeSetResource;
    }

    /**
     * @param array &$data
     */
    public function process(array &$data): void
    {
        if (empty($data['schedules'])) {
            return;
        }

        $timeSets = [];

        foreach ($data['schedules'] as $schedule) {
            if (!empty($schedule['schedule_id'])) {
                if (!empty($schedule['time_set_id'])) {
                    $timeIntervalSet = $timeSets[$schedule['time_set_id']]
                        ?? $this->timeSetGetter->execute((int)$schedule['time_set_id']);
                    $scheduleIds = $timeIntervalSet->getScheduleIds();
                    $scheduleIds[] = $schedule['schedule_id'];

                    $timeIntervalSet->setScheduleIds(array_unique($scheduleIds));
                    $timeSets[$schedule['time_set_id']] = $timeIntervalSet;
                } else {
                    $timeIntervalSet = $this->getEmptyTimeSet($schedule);
                    if ($timeIntervalSet) {
                        $timeSets[$timeIntervalSet->getId()] = $timeIntervalSet;
                    }
                }
            }
        }

        $data[self::TIME_SETS_KEY] = $timeSets;
    }

    /**
     * @param array $schedule
     * @return DataModel|null
     */
    private function getEmptyTimeSet(array $schedule): ?DataModel
    {
        $scheduleId = $schedule['schedule_id'];
        // need to know time set id
        $scheduleSetRelation = $this->timeSetResource->loadSetIdsByRelationIds(
            TimeIntervalSetResource::RELATION_TYPE_SCHEDULE,
            [$scheduleId]
        );

        if (isset($scheduleSetRelation[$scheduleId])) {
            $timeSetId = (int)$scheduleSetRelation[$scheduleId];
            $timeIntervalSet = $this->timeSetGetter->execute($timeSetId);
            // set empty data for delete from `amasty_deliverydate_time_intervals_set_relation`
            $timeIntervalSet->setChannelIds([]);
            // remove schedule id from time set to delete it, but stay other ids
            $timeIntervalSet->setScheduleIds(array_diff($timeIntervalSet->getScheduleIds(), [$scheduleId]));

            return $timeIntervalSet;
        }

        return null;
    }
}
