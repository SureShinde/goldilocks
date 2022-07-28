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

use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Reader;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Structure;

/**
 * @since 5.0.0
 */
class GetEventIds
{
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
     * @param array $categoryIds
     * @param int   $status
     * @param int   $days
     * @return array
     */
    public function execute(array $categoryIds, int $status, int $days): array
    {
        $result = $this->catalogEntityIndexHandler->readByEntities(EventType::CATEGORY, $categoryIds, $status, $days);
        return array_column($result, Structure::EVENT_ID);
    }
}
