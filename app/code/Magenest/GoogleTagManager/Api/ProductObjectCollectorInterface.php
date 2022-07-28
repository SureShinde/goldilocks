<?php

namespace Magenest\GoogleTagManager\Api;

/**
 * @api
 */
interface ProductObjectCollectorInterface
{
    /**
     * Generate productObject based on object
     *
     * @param mixed $fromObject Any product-like object (i.e. Catalog Product Model or Quote Item Model).
     * @param array $baseData
     *
     * @return string[]
     */
    public function collect($fromObject, array $baseData = []);
}
