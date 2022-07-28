<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api;

interface LimitableDataInterface
{
    public const LIMIT_ID = 'limit_id';

    /**
     * @return int|null
     */
    public function getLimitId(): ?int;

    /**
     * @param int|null $limitId
     *
     * @return void
     */
    public function setLimitId(?int $limitId): void;
}
