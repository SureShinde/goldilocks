<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Ui\DataProvider\LastPreorder\Listing;

use Amasty\PreOrderAnalytic\Model\IsPreorderOrdersExist;
use Amasty\PreOrderAnalytic\Model\ResourceModel\LoadLastPreorders;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var LoadLastPreorders
     */
    private $loadLastPreorders;

    /**
     * @var IsPreorderOrdersExist
     */
    private $isPreorderOrdersExist;

    public function __construct(
        IsPreorderOrdersExist $isPreorderOrdersExist,
        LoadLastPreorders $loadLastPreorders,
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
        $this->loadLastPreorders = $loadLastPreorders;
        $this->isPreorderOrdersExist = $isPreorderOrdersExist;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $items = $this->isPreorderOrdersExist->execute() ? $this->loadLastPreorders->execute() : [];
        return ['items' => $items];
    }
}
