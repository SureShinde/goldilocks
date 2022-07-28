<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\ServicesUi\Ui\Component\Listing\Consumer;

use Magenest\ServicesUi\Model\ResourceModel\Queue\Consumer\CollectionFactory;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var ReportingInterface
     */
    protected $reporting;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var SearchCriteriaInterface
     */
    private $searchCriteria;
    /**
     * @var SearchResultInterface
     */
    private $searchResult;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param SearchCriteriaInterface $searchCriteria
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ReportingInterface $reporting
     * @param SearchResultInterface $searchResult
     * @param array $meta
     * @param array $data
     */

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        SearchCriteriaInterface $searchCriteria,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ReportingInterface $reporting,
        SearchResultInterface $searchResult,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->searchCriteria = $searchCriteria;
        $this->searchResult = $searchResult;
        $this->reporting = $reporting;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->name = $name;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $collection = $this->getCollection();

        return $collection->toArray();
    }

    /**
     * @return SearchResultInterface
     */
    public function getSearchResult()
    {
        return $this->reporting->search($this->getSearchCriteria());
    }

    /**
     * @return \Magento\Framework\Api\Search\SearchCriteria|SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        $this->searchCriteria = $this->searchCriteriaBuilder->create();
        $this->searchCriteria->setRequestName($this->name);

        return $this->searchCriteria;
    }
}
