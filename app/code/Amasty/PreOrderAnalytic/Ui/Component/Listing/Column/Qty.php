<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Qty extends Column
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
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = (float) $item[$this->getData('name')];
            }
        }

        return $dataSource;
    }
}
