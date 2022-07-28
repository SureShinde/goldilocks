<?php

namespace Magenest\GoogleTagManager\Model\RequestInfo;

class ValueExtractor
{
    public function getSuperAttributes($requestInfo)
    {
        $attributes = [];

        if (\is_array($requestInfo) && \array_key_exists('super_attribute', $requestInfo) ||
            $requestInfo instanceof \ArrayAccess && $requestInfo->offsetExists('super_attribute')
        ) {
            $attributes = $requestInfo['super_attribute'];
        }

        return $attributes;
    }

    public function getQuantity($requestInfo)
    {
        return $requestInfo['qty'] ?? null;
    }
}
