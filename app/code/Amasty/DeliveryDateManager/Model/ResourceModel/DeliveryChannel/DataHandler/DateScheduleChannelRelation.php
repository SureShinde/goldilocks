<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\DataHandler;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Model\Relations\DateScheduleChannelRelation\SaveScheduleOfChannel;
use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractDb\DataHandlerInterface;
use Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\DataPreprocessor\DateScheduleChannelRelation as Preprocessor;
use Magento\Framework\Model\AbstractModel;

class DateScheduleChannelRelation implements DataHandlerInterface
{
    /**
     * @var SaveScheduleOfChannel
     */
    private $saveScheduleOfChannel;

    public function __construct(SaveScheduleOfChannel $saveScheduleOfChannel)
    {
        $this->saveScheduleOfChannel = $saveScheduleOfChannel;
    }

    /**
     * @param AbstractModel|DeliveryChannelInterface $model
     */
    public function afterSave(AbstractModel $model): void
    {
        $channelId = (int)$model->getId();
        $scheduleIds = (array)$model->getData(Preprocessor::SCHEDULE_IDS_KEY);

        $this->saveScheduleOfChannel->save([$channelId], $scheduleIds);
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
