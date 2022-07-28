<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Channel\Modal;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Model\DateSchedule\Get;
use Amasty\DeliveryDateManager\Model\DateSchedule\Type;
use Amasty\DeliveryDateManager\Model\DateSchedule\Type\ConvertToOutput;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * @method Collection getCollection()
 */
class DateScheduleDataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Get
     */
    private $scheduleGetter;

    /**
     * @var ConvertToOutput
     */
    private $outputConverter;

    /**
     * @var array
     */
    private $convertableTypes;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        Get $scheduleGetter,
        ConvertToOutput $outputConverter,
        array $convertableTypes = [
            Type::DAY_OF_MONTH,
            Type::DAY_OF_WEEK
        ],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->scheduleGetter = $scheduleGetter;
        $this->outputConverter = $outputConverter;
        $this->collection = $collectionFactory->create();
        $this->convertableTypes = $convertableTypes;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if ($scheduleId = (int)$this->request->getParam($this->getRequestFieldName())) {
            $schedule = $this->scheduleGetter->execute($scheduleId);
            $scheduleType = $schedule->getType();
            $from = $schedule->getFrom();
            $to = $schedule->getTo();

            if (in_array($scheduleType, $this->convertableTypes, true)) {
                $from = $this->outputConverter->execute($scheduleType, $from);
                $to = $this->outputConverter->execute($scheduleType, $to);
            }

            $this->data[$scheduleId] = [
                DateScheduleInterface::SCHEDULE_ID => $scheduleId,
                DateScheduleInterface::NAME => $schedule->getName(),
                DateScheduleInterface::TYPE => (string)$scheduleType,
                DateScheduleInterface::FROM . '_' . $scheduleType => $from,
                DateScheduleInterface::TO . '_' . $scheduleType => $to,
                DateScheduleInterface::IS_AVAILABLE => $schedule->getIsAvailable()
            ];
        }

        return $this->data;
    }
}
