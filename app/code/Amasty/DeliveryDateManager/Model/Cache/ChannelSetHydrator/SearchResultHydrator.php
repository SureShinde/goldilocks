<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Cache\ChannelSetHydrator;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\ObjectManagerInterface;

class SearchResultHydrator
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $data
     * @param string $searchResultsName class name of a Search result
     * @param string $dataModelName class name of a Data Model, items of Search Result
     *
     * @return SearchResultsInterface
     */
    public function hydrateSearchResult(
        array $data,
        string $searchResultsName,
        string $dataModelName
    ): SearchResultsInterface {
        /** @var SearchResultsInterface $searchResult */
        $searchResult = $this->objectManager->create($searchResultsName);
        $items = [];
        foreach ($data['items'] as $key => $itemData) {
            $items[$key] = $this->objectManager->create($dataModelName, ['data' => $itemData]);
        }

        $searchResult->setItems($items);
        $searchResult->setTotalCount($data['total_count']);

        return $searchResult;
    }
}
