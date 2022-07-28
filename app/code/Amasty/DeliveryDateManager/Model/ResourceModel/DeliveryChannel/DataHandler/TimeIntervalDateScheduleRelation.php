<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\DataHandler;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\DataPreprocessor\TimeIntervalDateScheduleRelation as Preprocessor;
use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractDb\DataHandlerInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set as TimeIntervalSetResource;
use Amasty\DeliveryDateManager\Model\TimeInterval\Set\DataModel as SetDataModel;
use Amasty\DeliveryDateManager\Model\TimeInterval\Set\ProcessRelationsSave;
use Magento\Framework\Model\AbstractModel;

class TimeIntervalDateScheduleRelation implements DataHandlerInterface
{
    /**
     * @var ProcessRelationsSave
     */
    private $processRelationsSave;

    /**
     * @var TimeIntervalSetResource
     */
    private $timeSetResource;

    public function __construct(
        ProcessRelationsSave $processRelationsSave,
        TimeIntervalSetResource $timeSetResource
    ) {
        $this->processRelationsSave = $processRelationsSave;
        $this->timeSetResource = $timeSetResource;
    }

    /**
     * @param AbstractModel|DeliveryChannelInterface $model
     */
    public function afterSave(AbstractModel $model): void
    {
        $timeSets = $model->getData(Preprocessor::TIME_SETS_KEY);
        if (!empty($timeSets)) {
            foreach ($timeSets as $timeSet) {
                if ($timeSet instanceof SetDataModel) {
                    $this->timeSetResource->save($timeSet);
                    $this->processRelationsSave->processSave($timeSet);
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
