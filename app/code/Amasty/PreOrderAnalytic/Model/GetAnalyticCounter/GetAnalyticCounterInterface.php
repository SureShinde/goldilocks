<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\GetAnalyticCounter;

interface GetAnalyticCounterInterface
{
    public function execute(array $params): int;
}
