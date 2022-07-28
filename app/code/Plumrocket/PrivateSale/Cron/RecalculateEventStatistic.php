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

namespace Plumrocket\PrivateSale\Cron;

use Plumrocket\PrivateSale\Helper\Config;
use Plumrocket\PrivateSale\Model\Indexer\EventStatistic\Indexer;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory;

/**
 * @since 5.0.0
 */
class RecalculateEventStatistic
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\EventStatistic\Indexer
     */
    private $indexer;

    /**
     * @param \Plumrocket\PrivateSale\Helper\Config                        $config
     * @param \Plumrocket\PrivateSale\Model\Indexer\EventStatistic\Indexer $indexer
     */
    public function __construct(
        Config $config,
        Indexer $indexer
    ) {
        $this->config = $config;
        $this->indexer = $indexer;
    }

    public function execute()
    {
        if ($this->config->isModuleEnabled()) {
            $this->indexer->executeFull();
        }
    }
}
