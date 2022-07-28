<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

interface DeliveryChannelInterface extends
    \Amasty\DeliveryDateManager\Api\LimitableDataInterface,
    \Amasty\DeliveryDateManager\Api\DataWithNameInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const CHANNEL_ID = 'channel_id';
    public const HAS_ORDER_COUNTER = 'has_order_counter';
    public const PRIORITY = 'priority';
    public const CONFIG_ID = 'config_id';
    public const IS_ACTIVE = 'is_active';

    /**
     * @return int
     */
    public function getChannelId(): int;

    /**
     * @param int|null $channelId
     *
     * @return void
     */
    public function setChannelId(?int $channelId): void;

    /**
     * @return int
     */
    public function getHasOrderCounter(): int;

    /**
     * @param int $hasOrderCounter
     *
     * @return void
     */
    public function setHasOrderCounter(int $hasOrderCounter): void;

    /**
     * @return int
     */
    public function getPriority(): int;

    /**
     * @param int $priority
     *
     * @return void
     */
    public function setPriority(int $priority): void;

    /**
     * @return int|null
     */
    public function getConfigId(): ?int;

    /**
     * @param int|null $configId
     *
     * @return void
     */
    public function setConfigId(?int $configId): void;

    /**
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * @param bool $isActive
     *
     * @return void
     */
    public function setIsActive(bool $isActive): void;
}
