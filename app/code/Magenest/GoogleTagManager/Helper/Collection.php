<?php

namespace Magenest\GoogleTagManager\Helper;

class Collection
{
    public function getOffset($collection)
    {
        if ($collection instanceof \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection) {
            return ($collection->getCurPage() - 1) * $collection->getPageSize();
        }

        return 0;
    }

    public function getItems($collection)
    {
        if ($collection instanceof \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection) {
            $items = $collection->getItems();
        } else {
            $items = \is_array($collection) ? $collection : [];
        }

        return \array_values($items);
    }
}
