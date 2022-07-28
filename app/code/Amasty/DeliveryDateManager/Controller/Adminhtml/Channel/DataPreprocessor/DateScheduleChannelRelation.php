<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\DataPreprocessor;

use Amasty\DeliveryDateManager\Model\Preprocessor\PreprocessorInterface;

class DateScheduleChannelRelation implements PreprocessorInterface
{
    public const SCHEDULE_IDS_KEY = 'prepared_schedule_ids_for_save';

    /**
     * @param array &$data
     */
    public function process(array &$data): void
    {
        $scheduleIds = [];
        $exceptionIds = [];
        if (!empty($data['schedules'])) {
            $scheduleIds = $this->getScheduleIds($data['schedules']);
        }
        if (!empty($data['exceptions'])) {
            $exceptionIds = $this->getScheduleIds($data['exceptions']);
        }
        $scheduleIds = array_merge($scheduleIds, $exceptionIds);
        $data[self::SCHEDULE_IDS_KEY] = $scheduleIds;
    }

    /**
     * @param array $schedules or $exceptions
     * @return array
     */
    private function getScheduleIds(array $schedules): array
    {
        $ids = [];
        foreach ($schedules as $schedule) {
            if (!empty($schedule['schedule_id'])) {
                $ids[] = $schedule['schedule_id'];
            }
        }

        return $ids;
    }
}
