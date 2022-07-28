<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class IncrementId extends Column
{
    /**
     * @var int
     */
    private $incrementId = 0;

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
                $incrementData = [$this->getData('name') => ++$this->incrementId];
                $item = $incrementData + $item;
            }
        }

        return $dataSource;
    }
}
