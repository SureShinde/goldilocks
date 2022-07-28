<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\GetAnalyticCounter;

use Amasty\PreOrderAnalytic\Model\ConfigProvider;
use Amasty\PreOrderAnalytic\Model\ResourceModel\LoadCountPlacedPreorders;

class GetCountPendingPreorders implements GetAnalyticCounterInterface
{
    /**
     * @var LoadCountPlacedPreorders
     */
    private $loadCountPlacedPreorders;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(LoadCountPlacedPreorders $loadCountPlacedPreorders, ConfigProvider $configProvider)
    {
        $this->loadCountPlacedPreorders = $loadCountPlacedPreorders;
        $this->configProvider = $configProvider;
    }

    public function execute(array $params): int
    {
        $params['status'] = ['in' => $this->configProvider->getPendingOrderStatuses()];

        return $this->loadCountPlacedPreorders->execute($params);
    }
}
