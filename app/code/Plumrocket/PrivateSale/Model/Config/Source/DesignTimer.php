<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\Config\Source;

use Plumrocket\PrivateSale\Helper\Timer;

class DesignTimer extends AbstractOptionSource
{
    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            Timer::DISABLED             => __('Disabled'),
            Timer::COUNTDOWN_DAYS_HOURS => __('Countdown (Days & Hours)'),
            Timer::COUNTDOWN_ALL        => __('Countdown (Days/Hours/Minutes/Seconds)'),
            Timer::STATIC_DATE          => __('Static End Date'),
        ];
    }
}
