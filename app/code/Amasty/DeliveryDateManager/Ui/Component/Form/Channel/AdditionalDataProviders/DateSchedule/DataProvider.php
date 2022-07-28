<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Channel\AdditionalDataProviders\DateSchedule;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Model\DateSchedule\Type\ConvertToOutput;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule\CollectionFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set as TimeSetResource;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * @method Collection getCollection()
 */
class DataProvider extends AbstractDataProvider
{
    public const BEHAVIOR_ALLOW = 1;
    public const BEHAVIOR_DISALLOW = 0;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var TimeSetResource
     */
    private $timeSetResource;

    /**
     * @var ConvertToOutput
     */
    private $outputConverter;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private $behaviorType;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        TimeSetResource $timeSetResource,
        ConvertToOutput $outputConverter,
        RequestInterface $request,
        $behaviorType = self::BEHAVIOR_ALLOW,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->timeSetResource = $timeSetResource;
        $this->request = $request;
        $this->behaviorType = $behaviorType;
        $this->outputConverter = $outputConverter;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $collection = $this->getCollection();

        // Exclude already used schedules from available options
        if ($this->behaviorType === self::BEHAVIOR_ALLOW) {
            $collection->addNotUsedFilter((int)$this->request->getParam('channel_id', 0));
        }

        $schedules = $collection
            ->addFieldToFilter(DateScheduleInterface::IS_AVAILABLE, $this->behaviorType)
            ->getData();

        if ($schedules) {
            $dateScheduleIds = $collection->getColumnValues(DateScheduleInterface::SCHEDULE_ID);
            $scheduleSetRelation = $this->timeSetResource->loadSetIdsByRelationIds(
                TimeSetResource::RELATION_TYPE_SCHEDULE,
                $dateScheduleIds
            );
            foreach ($schedules as &$scheduleData) {
                $id = $scheduleData[DateScheduleInterface::SCHEDULE_ID];
                $scheduleType = (int)$scheduleData[DateScheduleInterface::TYPE];
                $scheduleData[DateScheduleInterface::FROM] = $this->outputConverter->execute(
                    $scheduleType,
                    $scheduleData[DateScheduleInterface::FROM]
                );
                $scheduleData[DateScheduleInterface::TO] = $this->outputConverter->execute(
                    $scheduleType,
                    $scheduleData[DateScheduleInterface::TO]
                );

                if (isset($scheduleSetRelation[$id])) {
                    $scheduleData['time_set_id'] = (int)$scheduleSetRelation[$id];
                }
            }
        }

        return $schedules;
    }
}
