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
 * Utils class for index row
 *
 * @since 5.0.0
 */
class IndexRow
{
    /**
     * @var array
     */
    private $rowData;

    /**
     * @param array $rowData
     */
    public function __construct(array $rowData)
    {
        $this->rowData = $rowData;
    }

    public function getEntityId(): int
    {
        return (int) $this->rowData[Structure::ENTITY_ID];
    }

    public function getType(): int
    {
        return (int) $this->rowData[Structure::TYPE];
    }

    public function getNewUsers(): int
    {
        return (int) $this->rowData[Structure::NEW_USERS];
    }

    public function getOrderCount(): int
    {
        return (int) $this->rowData[Structure::ORDER_COUNT];
    }

    public function getTotalRevenue()
    {
        return (float) $this->rowData[Structure::TOTAL_REVENUE];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->rowData;
    }
}
