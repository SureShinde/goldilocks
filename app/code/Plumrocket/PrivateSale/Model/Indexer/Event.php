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

use Magento\Framework\Indexer\ActionInterface;
use Plumrocket\PrivateSale\Model\Catalog\Category\GetProductIds as GetCategoriesProductIds;
use Plumrocket\PrivateSale\Model\Config\Source\EventStatus;
use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Structure;
use Plumrocket\PrivateSale\Model\Store\FrontendWebsites;

/**
 * Reindex product data by event ids
 *
 * @since 5.0.0
 */
class Event implements ActionInterface
{
    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\Product
     */
    private $productEventIndexer;

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Reader
     */
    private $entityToEventIndexReader;

    /**
     * @var GetCategoriesProductIds
     */
    private $getCategoriesProductIds;

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\Builder
     */
    private $indexBuilder;

    /**
     * @var \Plumrocket\PrivateSale\Model\Store\FrontendWebsites
     */
    private $frontendWebsites;

    /**
     * @param \Plumrocket\PrivateSale\Model\Indexer\Product                $productEventIndexer
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Reader   $entityToEventIndexReader
     * @param \Plumrocket\PrivateSale\Model\Catalog\Category\GetProductIds $getCategoriesProductIds
     * @param \Plumrocket\PrivateSale\Model\Indexer\Builder                $indexBuilder
     * @param \Plumrocket\PrivateSale\Model\Store\FrontendWebsites         $frontendWebsites
     */
    public function __construct(
        Product $productEventIndexer,
        EntityToEvent\Reader $entityToEventIndexReader,
        GetCategoriesProductIds $getCategoriesProductIds,
        Builder $indexBuilder,
        FrontendWebsites $frontendWebsites
    ) {
        $this->productEventIndexer = $productEventIndexer;
        $this->entityToEventIndexReader = $entityToEventIndexReader;
        $this->getCategoriesProductIds = $getCategoriesProductIds;
        $this->indexBuilder = $indexBuilder;
        $this->frontendWebsites = $frontendWebsites;
    }

    /**
     * @inheritDoc
     */
    public function execute($ids)
    {
        $this->productEventIndexer->executeList($ids);
    }

    /**
     * @inheritDoc
     */
    public function executeFull()
    {
        $this->productEventIndexer->executeFull();
    }

    /**
     * @inheritDoc
     */
    public function executeList(array $ids)
    {
        $eventsData = $this->entityToEventIndexReader->readByEvents($ids);

        $this->indexBuilder->cleanEventIndex($ids, EventStatus::UPCOMING);
        $this->indexBuilder->cleanEventIndex($ids, EventStatus::ACTIVE);
        $this->indexBuilder->cleanEventIndex($ids, EventStatus::ENDED);

        if ($eventsData) {
            foreach ($this->frontendWebsites->getList() as $website) {
                $productIdsArrays = [];
                foreach ($eventsData as $eventData) {
                    if (EventType::CATEGORY === (int) $eventData[Structure::TYPE]) {
                        $productIdsArrays[] = $this->getCategoriesProductIds->execute(
                            [$eventsData[Structure::ENTITY_ID]]
                        );
                    } else {
                        $productIdsArrays[] = [(int) $eventData[Structure::ENTITY_ID]];
                    }
                }

                $this->productEventIndexer->executeListByWebsite(
                    array_unique(array_merge(...$productIdsArrays)),
                    $website
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function executeRow($id)
    {
        $this->productEventIndexer->executeList([$id]);
    }
}
