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

namespace Plumrocket\PrivateSale\Model\Event;

use Plumrocket\PrivateSale\Api\GetEventIdByProductIdInterface;
use Plumrocket\PrivateSale\Helper\Preview;
use Plumrocket\PrivateSale\Model\Indexer\IndexHandler;
use Plumrocket\PrivateSale\Model\Indexer\IndexStructure;

class GetEventIdByProductId implements GetEventIdByProductIdInterface
{
    /**
     * @var Preview
     */
    protected $previewHelper;

    /**
     * @var IndexHandler
     */
    private $indexHandler;

    /**
     * GetEventIdByCategoryId constructor.
     *
     * @param IndexHandler $indexHandler
     */
    public function __construct(IndexHandler $indexHandler)
    {
        $this->indexHandler = $indexHandler;
    }

    /**
     * @inheritDoc
     */
    public function execute(int $productId): int
    {
        $indexData = $this->indexHandler->readOne($productId);
        return isset($indexData[IndexStructure::EVENT_ID]) ? (int) $indexData[IndexStructure::EVENT_ID] : 0;
    }
}
