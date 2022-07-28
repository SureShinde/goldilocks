<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Ui\Component\Listing\Column\LastOrders;

use Amasty\PreOrderAnalytic\Model\ResourceModel\LoadLastPreorders;
use Magento\Sales\Ui\Component\Listing\Column\Price as SalesPrice;

class Price extends SalesPrice
{
    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                unset($item[LoadLastPreorders::CURRENCY_CODE]);
            }
        }

        return $dataSource;
    }
}
