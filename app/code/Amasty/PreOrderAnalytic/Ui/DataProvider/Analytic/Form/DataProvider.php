<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Ui\DataProvider\Analytic\Form;

use Amasty\PreOrderAnalytic\Model\AnalyticForm\DateRange\GetDefaultFrom;
use Amasty\PreOrderAnalytic\Model\AnalyticForm\DateRange\GetDefaultTo;
use Amasty\PreOrderAnalytic\Model\AnalyticForm\GetAllData;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var GetAllData
     */
    private $getAllData;

    /**
     * @var GetDefaultFrom
     */
    private $getDefaultFrom;

    /**
     * @var GetDefaultTo
     */
    private $getDefaultTo;

    public function __construct(
        GetAllData $getAllData,
        GetDefaultFrom $getDefaultFrom,
        GetDefaultTo $getDefaultTo,
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
        $this->getAllData = $getAllData;
        $this->getDefaultFrom = $getDefaultFrom;
        $this->getDefaultTo = $getDefaultTo;
    }

    public function getData()
    {
        $data = $this->getAllData->execute();

        if (!$this->request->isAjax()) {
            $data['date_range'] = [
                'to' => $this->getDefaultTo->execute(),
                'from' => $this->getDefaultFrom->execute()
            ];
        }

        return [
            null => $data
        ];
    }
}
