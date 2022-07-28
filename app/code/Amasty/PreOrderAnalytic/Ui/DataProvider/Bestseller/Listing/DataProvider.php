<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Ui\DataProvider\Bestseller\Listing;

use Amasty\PreOrderAnalytic\Model\Bestseller\GetItems;
use Amasty\PreOrderAnalytic\Model\IsPreorderOrdersExist;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{

    /**
     * @var IsPreorderOrdersExist
     */
    private $isPreorderOrdersExist;

    /**
     * @var GetItems
     */
    private $getItems;

    public function __construct(
        IsPreorderOrdersExist $isPreorderOrdersExist,
        GetItems $getItems,
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->isPreorderOrdersExist = $isPreorderOrdersExist;
        $this->getItems = $getItems;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $items = $this->isPreorderOrdersExist->execute() ? $this->getItems->execute() : [];
        return ['items' => $items];
    }
}
