<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Grid\Channel\Modifiers;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Schedules implements ModifierInterface
{
    public const SCHEDULES_KEY = 'schedules';
    public const SCHEDULE_NAMES_KEY = 'schedule_names';

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        foreach ($data['items'] as &$channelData) {
            $schedules = $channelData[self::SCHEDULES_KEY] ?? null;

            if (is_array($schedules)) {
                $scheduleNames = [];
                foreach ($schedules as $scheduleData) {
                    $scheduleNames[] = $scheduleData[DateScheduleInterface::NAME];
                }
                $channelData[self::SCHEDULE_NAMES_KEY] = implode(', ', $scheduleNames);
            }
        }

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
