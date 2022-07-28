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

namespace Plumrocket\PrivateSale\Model\Indexer;

use Plumrocket\PrivateSale\Model\Config\Source\EventStatus;
use Plumrocket\PrivateSale\Model\Indexer\Product as ProductEventIndexer;

/**
 * @since 5.0.0
 */
class TableNameResolver
{
    /**
     * @var array
     */
    private $indexTableMapping;

    /**
     * TableNameResolver constructor.
     *
     * @param array $indexTableMapping
     */
    public function __construct(array $indexTableMapping = [])
    {
        $this->indexTableMapping = $indexTableMapping;
    }

    /**
     * Retrieve table name of indexer
     *
     * @param string $indexName
     * @return string|null
     */
    public function getResolvedName(string $indexName)
    {
        return $this->indexTableMapping[$indexName] ?? null;
    }

    /**
     * @param int $simpleStatus
     * @return string|null
     */
    public function getResolvedNameByStatus(int $simpleStatus)
    {
        switch ($simpleStatus) {
            case EventStatus::UPCOMING:
                $indexName = ProductEventIndexer::UPCOMING_INDEX_NAME;
                break;
            case EventStatus::ACTIVE:
                $indexName = ProductEventIndexer::INDEX_NAME;
                break;
            case EventStatus::ENDED:
                $indexName = ProductEventIndexer::ENDED_INDEX_NAME;
                break;
            default:
                $indexName = '';
        }

        return $this->getResolvedName($indexName);
    }
}
