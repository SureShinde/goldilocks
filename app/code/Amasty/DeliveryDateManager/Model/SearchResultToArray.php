<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model;

class SearchResultToArray
{
    public function execute(\Magento\Framework\Api\SearchResultsInterface $searchResult): array
    {
        $arrItems = [
            'items' => [],
            'totalRecords' => $searchResult->getTotalCount()
        ];

        /** @var \Amasty\DeliveryDateManager\Model\AbstractTypifiedModel $item */
        foreach ($searchResult->getItems() as $item) {
            $arrItems['items'][] = $item->toArray();
        }

        return $arrItems;
    }

    public function getItems(\Magento\Framework\Api\SearchResultsInterface $searchResult): array
    {
        return $this->execute($searchResult)['items'];
    }
}
