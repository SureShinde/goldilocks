<?php

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Channel\Modifiers;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Model\DateSchedule\Provider;
use Amasty\DeliveryDateManager\Model\Relations\DateScheduleChannelRelation\RelationProvider;
use Amasty\DeliveryDateManager\Ui\Component\AbstractModifier;
use Magento\Framework\App\RequestInterface;

class DataSchedule extends AbstractModifier
{
    /**
     * @var RelationProvider
     */
    private $relationProvider;

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        RelationProvider $relationProvider,
        Provider $provider,
        RequestInterface $request
    ) {
        $this->relationProvider = $relationProvider;
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function modifyData(array $data): array
    {
        $channelId = $this->request->getParam(static::CHANNEL_REQUEST_ID);

        if (!$channelId) {
            return $data;
        }

        $scheduleIds = [];
        $schedulesData = [];
        $exceptionsData = [];
        $relationItems = $this->relationProvider->getListByChannelIds([$channelId])->getItems();

        foreach ($relationItems as $relation) {
            $scheduleIds[] = $relation->getDateScheduleId();
        }

        if ($scheduleIds) {
            $schedules = $this->provider->getScheduleByIds($scheduleIds)->getItems();

            foreach ($schedules as $schedule) {
                $row = [
                    DateScheduleInterface::SCHEDULE_ID => $schedule->getScheduleId()
                ];

                if ($schedule->getIsAvailable()) {
                    $schedulesData[] = $row;
                } else {
                    $exceptionsData[] = $row;
                }

            }
        }

        return array_replace_recursive(
            $data,
            [
                $channelId => [
                    static::FORM_GENERAL_SCHEDULE_RELATION => $schedulesData,
                    static::FORM_GENERAL_EXCEPTIONS_RELATION => $exceptionsData
                ]
            ]
        );
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
