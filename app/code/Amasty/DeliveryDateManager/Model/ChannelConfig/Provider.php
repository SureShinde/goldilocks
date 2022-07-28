<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ChannelConfig;

use Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface;
use Amasty\DeliveryDateManager\Api\Data\ChannelConfigSearchResultInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;

class Provider
{
    /**
     * @var GetList
     */
    private $getList;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $criteriaBuilderFactory;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    public function __construct(
        GetList $getList,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        FilterBuilder $filterBuilder
    ) {
        $this->getList = $getList;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @param int[] $ids
     *
     * @return ChannelConfigSearchResultInterface
     */
    public function getListByIds(array $ids): ChannelConfigSearchResultInterface
    {
        $this->filterBuilder->setField(ChannelConfigDataInterface::ID);
        $this->filterBuilder->setConditionType('in');
        $this->filterBuilder->setValue($ids);
        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder->addFilter($this->filterBuilder->create());

        return $this->getList->execute($criteriaBuilder->create());
    }
}
