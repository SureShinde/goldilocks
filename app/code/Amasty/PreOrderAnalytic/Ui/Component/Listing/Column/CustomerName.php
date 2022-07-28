<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class CustomerName extends Column
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
                $item[$this->getData('name')] = $item[$this->getData('name')]
                    ?: __('Guest')->render();
            }
        }

        return $dataSource;
    }
}
