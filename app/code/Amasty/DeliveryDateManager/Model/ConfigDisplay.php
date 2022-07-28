<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model;

/**
 * Returned bool for displayOn and include blocks
 */
class ConfigDisplay
{
    public const DATE = 'date';
    public const TIME = 'time';
    public const COMMENT = 'comment';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param string $place
     * @param int|null $store
     *
     * @return bool
     */
    public function isDateDisplayOn(string $place, int $store = null): bool
    {
        return in_array($place, $this->configProvider->getDateDisplayOn($store));
    }

    /**
     * @param string $place
     * @param int|null $store
     *
     * @return bool
     */
    public function isTimeDisplayOn(string $place, int $store = null): bool
    {
        return in_array($place, $this->configProvider->getTimeDisplayOn($store));
    }

    /**
     * @param string $place
     * @param int|null $store
     *
     * @return bool
     */
    public function isCommentDisplayOn(string $place, int $store = null): bool
    {
        return in_array($place, $this->configProvider->getCommentDisplayOn($store));
    }
}
