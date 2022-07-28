<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Grid\Channel\Filter\Source\TimeSet;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface;
use Amasty\DeliveryDateManager\Model\TimeInterval\GetList;
use Amasty\DeliveryDateManager\Model\TimeInterval\MinsToTimeConverter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class Interval implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var GetList
     */
    private $getList;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var MinsToTimeConverter
     */
    private $minsToTimeConverter;

    public function __construct(
        GetList $getList,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        MinsToTimeConverter $minsToTimeConverter
    ) {
        $this->getList = $getList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->minsToTimeConverter = $minsToTimeConverter;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $sortOrderByName = $this->sortOrderBuilder
                ->setField(TimeIntervalInterface::LABEL)
                ->setDescendingDirection()
                ->create();
            $sortOrder = $this->sortOrderBuilder
                ->setField(TimeIntervalInterface::FROM)
                ->setAscendingDirection()
                ->create();
            $searchCriteria = $this->searchCriteriaBuilder
                ->addSortOrder($sortOrderByName)
                ->addSortOrder($sortOrder)
                ->create();
            $intervalResult = $this->getList->execute($searchCriteria);
            $options = [];

            foreach ($intervalResult->getItems() as $interval) {
                $options[] = [
                    'value' => $interval->getIntervalId(),
                    'label' => $this->getLabel($interval)
                ];
            }
            $this->options = $options;
        }

        return $this->options;
    }

    /**
     * @param TimeIntervalInterface $timeInterval
     * @return string
     */
    private function getLabel(TimeIntervalInterface $timeInterval): string
    {
        $label = $this->minsToTimeConverter->execute($timeInterval->getFrom())
            . ' - ' . $this->minsToTimeConverter->execute($timeInterval->getTo());

        if ($timeInterval->getLabel()) {
            $label .= ' ' . $timeInterval->getLabel();
        }

        return $label;
    }
}
