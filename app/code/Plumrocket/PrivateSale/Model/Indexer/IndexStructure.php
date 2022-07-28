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

namespace Plumrocket\PrivateSale\Model\Indexer;

class IndexStructure
{
    const PRODUCT_ID = 'product_id';
    const WEBSITE_ID = 'website_id';
    const EVENT_ID = 'event_id';
    const IS_PRIVATE = 'is_private';
    const EVENT_FROM = 'start_date';
    const EVENT_TO = 'end_date';

    /**
     * Retrieve event products index table columns
     *
     * @return array
     */
    public function getColumns(): array
    {
        return [
            self::PRODUCT_ID,
            self::WEBSITE_ID,
            self::EVENT_ID,
            self::IS_PRIVATE,
            self::EVENT_FROM,
            self::EVENT_TO,
        ];
    }
}
