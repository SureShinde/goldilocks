<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

use Amasty\DeliveryDateManager\Api\DataWithNameInterface;

/**
 * Channel Configuration Data
 */
interface ChannelConfigDataInterface extends DataWithNameInterface
{
    public const ID = 'id';
    public const MIN = 'min';
    public const MAX = 'max';
    public const IS_SAME_DAY_AVAILABLE = 'is_same_day_available';
    public const SAME_DAY_CUTOFF = 'same_day_cutoff';
    public const ORDER_TIME = 'order_time';
    public const BACKORDER_TIME = 'backorder_time';

    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param int|null $configId
     *
     * @return void
     */
    public function setConfigId(?int $configId): void;

    /**
     * @return int|null
     */
    public function getMin(): ?int;

    /**
     * @param int|null $min
     * @return void
     */
    public function setMin(?int $min): void;

    /**
     * @return int|null
     */
    public function getMax(): ?int;

    /**
     * @param int|null $max
     * @return void
     */
    public function setMax(?int $max): void;

    /**
     * @return bool|null
     */
    public function getIsSameDayAvailable(): ?bool;

    /**
     * @param bool|null $isAvailable
     * @return void
     */
    public function setSameDayAvailable(?bool $isAvailable): void;

    /**
     * @return int|null
     */
    public function getSameDayCutoff(): ?int;

    /**
     * @param int|null $sameDayCutoff
     * @return void
     */
    public function setSameDayCutoff(?int $sameDayCutoff): void;

    /**
     * @return int|null
     */
    public function getOrderTime(): ?int;

    /**
     * @param int|null $orderTime
     * @return void
     */
    public function setOrderTime(?int $orderTime): void;

    /**
     * @return int|null
     */
    public function getBackorderTime(): ?int;

    /**
     * @param int|null $backorderTime
     * @return void
     */
    public function setBackorderTime(?int $backorderTime): void;
}
