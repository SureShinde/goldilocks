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
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\Event\Product;

/**
 * Retrieve all single product event ids or for specified status
 *
 * @since 5.0.0
 */
class GetAllEventsIds
{
    /**
     * @var array[]
     */
    private $cache = [];

    /**
     * @var \Plumrocket\PrivateSale\Model\Event\Product\GetEventIds
     */
    private $getEventIds;

    /**
     * @param \Plumrocket\PrivateSale\Model\Event\Product\GetEventIds $getEventIds
     */
    public function __construct(GetEventIds $getEventIds)
    {
        $this->getEventIds = $getEventIds;
    }

    /**
     * @param int|null $status
     * @param int|null $days
     * @param bool     $forceUpdate
     * @return array
     */
    public function execute(int $status = null, int $days = null, bool $forceUpdate = false): array
    {
        $key = (int) $status . '_' . (int) $days;
        $keyAllEvents = '0_0';

        if ($forceUpdate) {
            $this->cache[$keyAllEvents] = null;
            $this->cache[$key] = null;
        }

        if (! isset($this->cache[$key])) {
            $this->cache[$key] = $this->getEventIds->execute([], $status, $days);
        }

        return $this->cache[$key];
    }
}
