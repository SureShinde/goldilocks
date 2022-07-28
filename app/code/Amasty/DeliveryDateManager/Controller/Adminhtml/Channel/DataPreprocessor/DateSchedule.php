<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\DataPreprocessor;

use Amasty\DeliveryDateManager\Model\DateSchedule\DateScheduleData;
use Amasty\DeliveryDateManager\Model\DateSchedule\Provider;
use Amasty\DeliveryDateManager\Model\Preprocessor\PreprocessorInterface;

class DateSchedule implements PreprocessorInterface
{
    public const SCHEDULES_KEY = 'prepared_schedules_for_save';
    public const SCHEDULE_IDS_KEY = 'schedule_ids';

    /**
     * @var Provider
     */
    private $scheduleProvider;

    public function __construct(Provider $scheduleProvider)
    {
        $this->scheduleProvider = $scheduleProvider;
    }

    /**
     * @param array &$data
     */
    public function process(array &$data): void
    {
        if (empty($data['schedules'])) {
            return;
        }

        $schedulesForSave = $this->getSchedulesForSave($data['schedules']);
        if (!empty($schedulesForSave)) {
            $dateScheduleItems = $this->scheduleProvider->getScheduleByIds(array_keys($schedulesForSave));
            /** @var DateScheduleData $item */
            foreach ($dateScheduleItems->getItems() as $item) {
                $scheduleId = $item->getScheduleId();
                $limitId = $schedulesForSave[$scheduleId];
                $item->setLimitId($limitId);
                $data[self::SCHEDULES_KEY][$scheduleId] = $item;
                $data[self::SCHEDULE_IDS_KEY][] = $scheduleId;
            }
        }
    }

    /**
     * @param array $schedules
     * @return array
     */
    private function getSchedulesForSave(array $schedules): array
    {
        $schedulesForSave = [];
        foreach ($schedules as $scheduleData) {
            if (!empty($scheduleData['schedule_id'])) {
                $limitId = !empty($scheduleData['limit_id']) ? (int)$scheduleData['limit_id'] : null;
                $schedulesForSave[$scheduleData['schedule_id']] = $limitId;
            }
        }

        return $schedulesForSave;
    }
}
