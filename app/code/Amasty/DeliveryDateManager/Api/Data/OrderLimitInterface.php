<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

interface OrderLimitInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const LIMIT_ID = 'limit_id';
    public const DAY_LIMIT = 'day_limit';
    public const INTERVAL_LIMIT = 'interval_limit';
    public const NAME = 'name';

    /**
     * @return int
     */
    public function getLimitId(): int;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void;

    /**
     * @param int|null $limitId
     *
     * @return void
     */
    public function setLimitId(?int $limitId): void;

    /**
     * @return int|null
     */
    public function getDayLimit(): ?int;

    /**
     * @param int|null $dayLimit
     *
     * @return void
     */
    public function setDayLimit(?int $dayLimit): void;

    /**
     * @return int|null
     */
    public function getIntervalLimit(): ?int;

    /**
     * @param int|null $intervalLimit
     *
     * @return void
     */
    public function setIntervalLimit(?int $intervalLimit): void;
}
