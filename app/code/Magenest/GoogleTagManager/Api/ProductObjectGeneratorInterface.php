<?php

namespace Magenest\GoogleTagManager\Api;

/**
 * @api
 */
interface ProductObjectGeneratorInterface
{
    /**
     * Generate productObject based on passed in $fromObject param
     *
     * @param mixed $fromObject Any product-like object (i.e. Catalog Product Model or Quote Item Model).
     * @param array $baseData
     *
     * @return string[]
     */
    public function generate($fromObject, array $baseData = []);
}
