<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\GetAnalyticCounter;

use Amasty\PreOrderAnalytic\Model\ResourceModel\LoadCountPlacedPreorders;

class GetCountPlacedPreorders implements GetAnalyticCounterInterface
{
    /**
     * @var LoadCountPlacedPreorders
     */
    private $loadCountPlacedPreorders;

    public function __construct(LoadCountPlacedPreorders $loadCountPlacedPreorders)
    {
        $this->loadCountPlacedPreorders = $loadCountPlacedPreorders;
    }

    public function execute(array $params): int
    {
        return $this->loadCountPlacedPreorders->execute($params);
    }
}
