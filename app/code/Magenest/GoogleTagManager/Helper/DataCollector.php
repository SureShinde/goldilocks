<?php

namespace Magenest\GoogleTagManager\Helper;

class DataCollector
{
    /**
     * @param array $collectors
     * @param \Closure $callback
     * @return array
     */
    public function walkCollectors(array $collectors, \Closure $callback)
    {
        $data = \array_reduce($collectors, function ($data, $collector) use ($callback) {
            return \array_replace($data, $callback($collector));
        }, []);

        return \array_filter($data, function ($item) {
            return $item !== null;
        });
    }
}
