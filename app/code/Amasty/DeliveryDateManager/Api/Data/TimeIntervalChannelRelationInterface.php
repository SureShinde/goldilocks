<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

interface TimeIntervalChannelRelationInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const RELATION_ID = 'relation_id';
    public const DELIVERY_CHANNEL_ID = 'delivery_channel_id';
    public const TIME_INTERVAL_ID = 'time_interval_id';

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
    public function getTimeIntervalId(): int;

    /**
     * @param int $timeIntervalId
     *
     * @return void
     */
    public function setTimeIntervalId(int $timeIntervalId): void;
}
