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

namespace Plumrocket\PrivateSale\Model\EventStatistics;

use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Plumrocket\PrivateSale\Model\Indexer\EventStatistic\Reader;

/**
 * @since 5.0.0
 */
class IndexData
{
    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\EventStatistic\Reader
     */
    private $indexReader;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;

    /**
     * @param \Plumrocket\PrivateSale\Model\Indexer\EventStatistic\Reader $indexReader
     * @param \Magento\Framework\Pricing\Helper\Data                      $pricingHelper
     */
    public function __construct(Reader $indexReader, PricingHelper $pricingHelper)
    {
        $this->indexReader = $indexReader;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * @param $items
     * @return mixed
     */
    public function applyToEventItems(array $items): array
    {
        $eventIds = array_column($items, 'entity_id');
        $indexRows = $this->indexReader->readByEvents($eventIds);

        return $this->fillItems($items, $indexRows);
    }

    /**
     * @param array $items
     * @return array
     */
    public function applyToHomepageItems(array $items): array
    {
        $categoriesIds = array_column($items, 'entity_id');
        $indexRows = $this->indexReader->readByHomepages($categoriesIds);

        return $this->fillItems($items, $indexRows);
    }

    /**
     * @param array $items
     * @param array $indexRows
     * @return array
     */
    private function fillItems(array $items, array $indexRows): array
    {
        $itemsWithStatistic = [];
        foreach ($items as $item) {
            $entityId = $item['entity_id'];
            if (isset($indexRows[$entityId])) {
                $indexRow = $indexRows[$entityId];
                $item['order_count'] = $indexRow->getOrderCount();
                $item['total_revenue'] = $this->formatPrice($indexRow->getTotalRevenue());
                $item['new_users'] = $indexRow->getNewUsers();
            } else {
                $item['order_count'] = 0;
                $item['total_revenue'] = 0;
                $item['new_users'] = 0;
            }
            $itemsWithStatistic[] = $item;
        }

        return $itemsWithStatistic;
    }

    /**
     * @param $price
     * @return float|string
     */
    private function formatPrice($price)
    {
        return $this->pricingHelper->currency(
            $price,
            true,
            false
        );
    }
}
