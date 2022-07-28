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

namespace Plumrocket\PrivateSale\Model\Indexer\EntityToEvent;

use Magento\Framework\App\ResourceConnection;

/**
 * @since 5.0.0
 */
class Builder
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param array                                                          $ids
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRow[] $data
     * @return bool
     */
    public function build(array $ids, array $data) : bool
    {
        $this->clear($ids);

        return $this->write($data);
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function clear(array $ids) : self
    {
        $connection = $this->resourceConnection->getConnection();

        $connection->delete(
            $this->resourceConnection->getTableName(Reader::MAIN_TABLE_NAME),
            [Structure::EVENT_ID . ' IN (?)' => $ids]
        );

        return $this;
    }

    /**
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRow[] $data
     * @return bool
     */
    public function write(array $data) : bool
    {
        if (! $data) {
            return false;
        }

        $data = array_map(static function (IndexRow $indexRow) {
            return $indexRow->toArray();
        }, $data);

        $connection = $this->resourceConnection->getConnection();

        $affectedRows = (int) $connection->insertMultiple(
            $this->resourceConnection->getTableName(Reader::MAIN_TABLE_NAME),
            $data
        );

        return count($data) === $affectedRows;
    }
}
