<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannel\Validator;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationInterface;
use Amasty\DeliveryDateManager\Model\DeliveryChannel\DeliveryChannelData;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation;
use Magento\Framework\Validator\AbstractValidator;

class Schedules extends AbstractValidator
{
    /**
     * @var DateScheduleChannelRelation
     */
    private $dateScheduleChannelRelation;

    public function __construct(
        DateScheduleChannelRelation $dateScheduleChannelRelation
    ) {
        $this->dateScheduleChannelRelation = $dateScheduleChannelRelation;
    }

    /**
     * @param DeliveryChannelData $channel
     * @return bool
     */
    public function isValid($channel): bool
    {
        $errors = [];

        if ($this->isSchedulesAlreadyUsed($channel->getChannelId(), (array)$channel->getScheduleIds())) {
            $errors[] = __('The schedule is already attached to another delivery channel. Please create another one.');
        }

        if (empty($errors)) {
            return true;
        }

        $this->_addMessages($errors);

        return false;
    }

    /**
     * @param int $channelId
     * @param array $channelScheduleIds
     * @return bool
     */
    private function isSchedulesAlreadyUsed(int $channelId, array $channelScheduleIds): bool
    {
        $relations = $this->dateScheduleChannelRelation->getChannelRelationByDataIds($channelScheduleIds);

        if (!$channelId && !empty($relations)) {
            return true;
        }

        foreach ($relations as $relation) {
            if ($channelId !== (int)$relation[DateScheduleChannelRelationInterface::DELIVERY_CHANNEL_ID]) {
                return true;
            }
        }

        return false;
    }
}