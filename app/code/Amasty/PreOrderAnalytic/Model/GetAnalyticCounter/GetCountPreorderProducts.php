<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\GetAnalyticCounter;

use Amasty\PreOrderAnalytic\Model\ResourceModel\LoadCountPreorderProducts;

class GetCountPreorderProducts implements GetAnalyticCounterInterface
{
    /**
     * @var LoadCountPreorderProducts
     */
    private $loadCountPreorderProducts;

    public function __construct(LoadCountPreorderProducts $loadCountPreorderProducts)
    {
        $this->loadCountPreorderProducts = $loadCountPreorderProducts;
    }

    public function execute(array $params): int
    {
        return $this->loadCountPreorderProducts->execute($params);
    }
}
