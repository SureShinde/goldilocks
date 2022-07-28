<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Grid\Channel\Filter\Source;

use Amasty\DeliveryDateManager\Model\DateSchedule\GetList;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class Schedule implements OptionSourceInterface
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

    public function __construct(
        GetList $getList,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->getList = $getList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $scheduleResult = $this->getList->execute($searchCriteria);
            $options = [];

            foreach ($scheduleResult->getItems() as $schedule) {
                $options[] = [
                    'value' => $schedule->getScheduleId(),
                    'label' => $schedule->getName()
                ];
            }
            $this->options = $options;
        }

        return $this->options;
    }
}
