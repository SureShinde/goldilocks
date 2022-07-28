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

/**
 * @since 5.0.0
 */
class IndexRowsFilter
{
    /**
     * @param \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRow[] $indexRows
     * @param int                                                            $status
     * @return array
     */
    public function filterBySimpleStatus(array $indexRows, int $status): array
    {
        $result = [];
        foreach ($indexRows as $indexRow) {
            if ($status === $indexRow->getSimpleStatus()) {
                $result[] = $indexRow;
            }
        }

        return $result;
    }
}
