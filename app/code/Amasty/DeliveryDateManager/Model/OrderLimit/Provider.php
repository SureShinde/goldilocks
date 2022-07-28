<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\OrderLimit;

use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Api\Data\OrderLimitSearchResultInterface;

class Provider
{
    /**
     * @var GetList
     */
    private $getList;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory
     */
    private $criteriaBuilderFactory;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    public function __construct(
        GetList $getList,
        \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->getList = $getList;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @param int[] $ids
     *
     * @return OrderLimitSearchResultInterface
     */
    public function getOrderLimitByIds(array $ids): OrderLimitSearchResultInterface
    {
        $this->filterBuilder->setField(OrderLimitInterface::LIMIT_ID);
        $this->filterBuilder->setConditionType('in');
        $this->filterBuilder->setValue($ids);
        /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder->addFilter($this->filterBuilder->create());

        return $this->getList->execute($criteriaBuilder->create());
    }
}
