<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

interface DateScheduleChannelRelationInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const RELATION_ID = 'relation_id';
    public const DELIVERY_CHANNEL_ID = 'delivery_channel_id';
    public const DATE_SCHEDULE_ID = 'date_schedule_id';

    /**
     * @return int
     */
    public function getRelationId(): int;

    /**
     * @param int $relationId
     *
     * @return void
     */
    public function setRelationId(int $relationId): void;

    /**
     * @return int
     */
    public function getDeliveryChannelId(): int;

    /**
     * @param int $deliveryChannelId
     *
     * @return void
     */
    public function setDeliveryChannelId(int $deliveryChannelId): void;

    /**
     * @return int
     */
    public function getDateScheduleId(): int;

    /**
     * @param int $dateScheduleId
     *
     * @return void
     */
    public function setDateScheduleId(int $dateScheduleId): void;
}
