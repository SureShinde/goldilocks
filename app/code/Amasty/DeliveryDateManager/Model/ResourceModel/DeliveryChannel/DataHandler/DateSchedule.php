<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\DataHandler;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\DataPreprocessor\DateSchedule as Preprocessor;
use Amasty\DeliveryDateManager\Model\DateSchedule\Save;
use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractDb\DataHandlerInterface;
use Magento\Framework\Model\AbstractModel;

class DateSchedule implements DataHandlerInterface
{
    /**
     * @var Save
     */
    private $scheduleSaver;

    public function __construct(Save $scheduleSaver)
    {
        $this->scheduleSaver = $scheduleSaver;
    }

    /**
     * Save Schedules
     *
     * @param AbstractModel|DeliveryChannelInterface $model
     */
    public function afterSave(AbstractModel $model): void
    {
        $schedules = $model->getData(Preprocessor::SCHEDULES_KEY);
        if (!empty($schedules)) {
            foreach ($schedules as $schedule) {
                if ($schedule instanceof DateScheduleInterface) {
                    $this->scheduleSaver->execute($schedule);
                }
            }
        }
    }

    /**
     * @param AbstractModel|DeliveryChannelInterface $model
     * @return void
     */
    public function afterLoad(AbstractModel $model): void
    {
        // Just do nothing in that case.
    }
}
