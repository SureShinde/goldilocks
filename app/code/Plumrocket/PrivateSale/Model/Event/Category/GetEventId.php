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

namespace Plumrocket\PrivateSale\Model\Event\Category;

use Plumrocket\PrivateSale\Api\GetEventIdByCategoryIdInterface;
use Plumrocket\PrivateSale\Model\Config\Source\EventStatus;
use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Reader;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Structure;

/**
 * @since 5.0.0
 */
class GetEventId implements GetEventIdByCategoryIdInterface
{
    /**
     * @var int[]
     */
    private $cache = [];

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Reader
     */
    private $catalogEntityIndexHandler;

    /**
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Reader $catalogEntityIndexHandler
     */
    public function __construct(Reader $catalogEntityIndexHandler)
    {
        $this->catalogEntityIndexHandler = $catalogEntityIndexHandler;
    }

    /**
     * @inheritDoc
     */
    public function execute(int $categoryId, int $status = EventStatus::ACTIVE, bool $forceUpdate = false): int
    {
        $key = "cat_{$categoryId}_status_{$status}";

        if ($forceUpdate || ! isset($this->cache[$key])) {
            $result = $this->catalogEntityIndexHandler->readOne(EventType::CATEGORY, $categoryId, $status);
            $this->cache[$key] = (int) ($result[Structure::EVENT_ID] ?? 0);
        }

        return $this->cache[$key];
    }
}
