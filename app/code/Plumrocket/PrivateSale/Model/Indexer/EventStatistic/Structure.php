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

namespace Plumrocket\PrivateSale\Model\Indexer\EventStatistic;

/**
 * @since 5.0.0
 */
class Structure
{
    const ID = 'id';
    const ENTITY_ID = 'entity_id';
    const TYPE = 'type';
    const NEW_USERS = 'new_users';
    const ORDER_COUNT = 'order_count';
    const TOTAL_REVENUE = 'total_revenue';

    /**
     * @return string[]
     */
    public function getColumns(): array
    {
        return [
            self::ID,
            self::ENTITY_ID,
            self::TYPE,
            self::NEW_USERS,
            self::ORDER_COUNT,
            self::TOTAL_REVENUE,
        ];
    }
}
