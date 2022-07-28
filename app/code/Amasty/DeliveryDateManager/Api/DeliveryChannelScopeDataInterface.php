<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api;

/**
 * Delivery Channel Scope Interface.
 * Channel have many scope.
 * Data model interface.
 */
interface DeliveryChannelScopeDataInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const SCOPE_ID = 'scope_id';
    public const CHANNEL_ID = 'channel_id';

    /**
     * Relation ID
     *
     * @return int
     */
    public function getScopeId(): int;

    /**
     * @param int $scopeId
     *
     * @return void
     */
    public function setScopeId(int $scopeId): void;

    /**
     * Related Channel ID
     *
     * @return int
     */
    public function getChannelId(): int;

    /**
     * @param int $channelId
     *
     * @return void
     */
    public function setChannelId(int $channelId): void;

    /**
     * @return string|int|bool
     */
    public function getScopeValue();

    /**
     * @param string|int|bool|null $scopeValue
     *
     * @return void
     */
    public function setScopeValue($scopeValue): void;
}
