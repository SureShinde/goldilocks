<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\AnalyticForm;

use Amasty\PreOrderAnalytic\Model\GetAnalyticCounter;
use Amasty\PreOrderAnalytic\Model\GetRevenuePreorder;
use Amasty\PreOrderAnalytic\Model\Request\GetFilterParams;

class GetAllData
{
    /**
     * @var GetFilterParams
     */
    private $getFilterParams;

    /**
     * @var GetAnalyticCounter
     */
    private $getAnalyticCounter;

    /**
     * @var GetRevenuePreorder
     */
    private $getRevenuePreorder;

    public function __construct(
        GetFilterParams $getFilterParams,
        GetAnalyticCounter $getAnalyticCounter,
        GetRevenuePreorder $getRevenuePreorder
    ) {
        $this->getFilterParams = $getFilterParams;
        $this->getAnalyticCounter = $getAnalyticCounter;
        $this->getRevenuePreorder = $getRevenuePreorder;
    }

    public function execute(): array
    {
        $filterParams = $this->getFilterParams->execute();

        return [
            'preorder_item' => $this->getAnalyticCounter->execute('preorder_item', $filterParams),
            'preorder_customer' => $this->getAnalyticCounter->execute('preorder_customer', $filterParams),
            'preorder_placed' => $this->getAnalyticCounter->execute('preorder_placed', $filterParams),
            'preorder_pending' => $this->getAnalyticCounter->execute('preorder_pending', $filterParams),
            'preorder_revenue' => $this->getRevenuePreorder->execute($filterParams)
        ];
    }
}
