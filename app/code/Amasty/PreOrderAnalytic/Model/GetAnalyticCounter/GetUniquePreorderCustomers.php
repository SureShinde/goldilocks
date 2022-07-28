<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\GetAnalyticCounter;

use Amasty\PreOrderAnalytic\Model\ResourceModel\LoadUniquePreorderCustomers;

class GetUniquePreorderCustomers implements GetAnalyticCounterInterface
{
    /**
     * @var LoadUniquePreorderCustomers
     */
    private $loadUniquePreorderCustomers;

    public function __construct(LoadUniquePreorderCustomers $loadUniquePreorderCustomers)
    {
        $this->loadUniquePreorderCustomers = $loadUniquePreorderCustomers;
    }

    public function execute(array $params): int
    {
        return $this->loadUniquePreorderCustomers->execute($params);
    }
}
