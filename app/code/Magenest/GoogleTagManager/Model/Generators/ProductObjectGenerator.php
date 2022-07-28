<?php

namespace Magenest\GoogleTagManager\Model\Generators;

class ProductObjectGenerator implements \Magenest\GoogleTagManager\Api\ProductObjectGeneratorInterface
{
    /**
     * @var \Magenest\GoogleTagManager\Api\ProductObjectCollectorInterface[]
     */
    private $collectors;

    /**
     * @param \Magenest\GoogleTagManager\Api\ProductObjectCollectorInterface[] $collectors
     */
    public function __construct(
        array $collectors = []
    ) {
        $this->collectors = $collectors;
    }

    public function generate($fromObject, array $baseData = []) : array
    {
        $data = $baseData;

        foreach ($this->collectors as $handler) {
            $data = $handler->collect($fromObject, $data);
        }

        return $data;
    }
}
