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

use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Reader;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Structure;

/**
 * Retrieve single product event ids by product ids
 *
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
     * @param array $productIds
     * @param int   $status
     * @param int   $days
     * @return array
     */
    public function execute(array $productIds, int $status, int $days): array
    {
        $indexRows = $this->catalogEntityIndexHandler->readByEntities(EventType::PRODUCT, $productIds, $status, $days);

        $productsToEvents = [];
        foreach ($indexRows as $row) {
            $productId = $row[Structure::ENTITY_ID];
            if (array_key_exists($productId, $productsToEvents)) {
                continue;
            }

            $productsToEvents[$productId] = $row[Structure::EVENT_ID];
        }

        return $productsToEvents;
    }
}
