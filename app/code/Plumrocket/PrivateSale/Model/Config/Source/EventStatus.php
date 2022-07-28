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

class EventStatus extends AbstractOptionSource
{
    const DISABLED = 0;
    const ACTIVE = 1;
    const ENDING_SOON = 2;
    const UPCOMING = 3;
    const ENDED = 4;
    const COMING_SOON = 5;

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            self::DISABLED => __('Disabled'),
            self::ACTIVE => __('Active'),
            self::ENDING_SOON => __('Ending Soon'),
            self::COMING_SOON => __('Coming Soon'),
            self::UPCOMING => __('Upcoming'),
            self::ENDED => ('Ended'),
        ];
    }
}
