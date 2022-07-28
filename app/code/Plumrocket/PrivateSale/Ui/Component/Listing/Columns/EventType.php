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
use Plumrocket\PrivateSale\Model\Config\Source\SaleType;

class EventType extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $currentField = $this->getData('name');

            foreach ($dataSource['data']['items'] as & $item) {
                if (! empty($item['is_event_private']) && $item['is_event_private']) {
                    $item[$currentField] = SaleType::PRIVATE;
                } else {
                    $item[$currentField] = SaleType::FLASH;
                }
            }
        }

        return parent::prepareDataSource($dataSource);
    }
}
