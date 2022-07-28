<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

use Amasty\DeliveryDateManager\Api\LimitableDataInterface;

interface TimeIntervalInterface extends LimitableDataInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const INTERVAL_ID = 'interval_id';
    public const FROM = 'from';
    public const TO = 'to';
    public const LABEL = 'label';
    public const POSITION = 'position';

    /**
     * @return string|null
     */
    public function getLabel(): ?string;

    /**
     * @param string $label
     *
     * @return void
     */
    public function setLabel(string $label): void;

    /**
     * @return int
     */
    public function getIntervalId(): int;

    /**
     * @param int|null $intervalId
     *
     * @return void
     */
    public function setIntervalId(?int $intervalId): void;

    /**
     * @return int
     */
    public function getFrom(): int;

    /**
     * @param int $from
     *
     * @return void
     */
    public function setFrom(int $from): void;

    /**
     * @return int
     */
    public function getTo(): int;

    /**
     * @param int $to
     *
     * @return void
     */
    public function setTo(int $to): void;

    /**
     * @return int
     */
    public function getPosition(): int;

    /**
     * @param int $position
     *
     * @return void
     */
    public function setPosition(int $position): void;
}
