<?php

namespace Magenest\WebApiLog\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class StatusColumn
 *
 * @package Magenest\WebApiLog\Ui\Component\Listing\Column
 */
class StatusColumn extends Column
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
            foreach ($dataSource['data']['items'] as & $item) {
                if (empty($item['exception'])) {
                    $item[$this->getData('name')] =
                        '<span class="grid-severity-notice"><span>Success</span></span>';
                } else {
                    $item[$this->getData('name')] =
                        '<span class="grid-severity-critical"><span>Error</span></span>';
                }
            }
        }

        return $dataSource;
    }
}
