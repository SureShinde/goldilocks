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

namespace Plumrocket\PrivateSale\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use Plumrocket\PrivateSale\Model\Config\Source\EventType as SourceEventTypes;

class ProductCount extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $currentField = $this->getData('name');
            $categories = $this->getContext()->getDataProvider()->getConfigData()['categories'];

            foreach ($dataSource['data']['items'] as & $item) {
                if ((int) $item['event_type'] === SourceEventTypes::CATEGORY) {
                    $item[$currentField] = isset($item['category_event'], $categories[$item['category_event']])
                        ? $categories[$item['category_event']]->getProductCount()
                        : 0;
                } else {
                    $item[$currentField] = 1;
                }
            }
        }

        return $dataSource;
    }
}
