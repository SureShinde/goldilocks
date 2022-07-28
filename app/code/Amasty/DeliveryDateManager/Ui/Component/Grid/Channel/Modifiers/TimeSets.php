<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Grid\Channel\Modifiers;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class TimeSets implements ModifierInterface
{
    public const TIME_SETS_KEY = 'time_sets';
    public const TIME_SET_NAMES_KEY = 'time_set_names';

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        foreach ($data['items'] as &$channelData) {
            $timeSets = $channelData[self::TIME_SETS_KEY] ?? null;

            if (is_array($timeSets)) {
                $timeSetNames = [];
                foreach ($timeSets as $timeSetData) {
                    $timeSetNames[] = $timeSetData['name'];
                }
                $channelData[self::TIME_SET_NAMES_KEY] = implode(', ', $timeSetNames);
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
