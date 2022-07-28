<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model;

use Amasty\PreOrderAnalytic\Model\GetAnalyticCounter\GetAnalyticCounterInterface;

class GetAnalyticCounter
{
    /**
     * @var GetAnalyticCounterInterface[]
     */
    private $getAnalyticCounterPool;

    public function __construct(array $getAnalyticCounterPool = [])
    {
        $this->getAnalyticCounterPool = $getAnalyticCounterPool;
    }

    public function execute(string $code, array $params = []): int
    {
        return isset($this->getAnalyticCounterPool[$code]) ? $this->getAnalyticCounterPool[$code]->execute($params) : 0;
    }
}
