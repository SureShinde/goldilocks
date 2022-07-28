<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannel;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\DataPreprocessor\DateScheduleChannelRelation;
use Amasty\DeliveryDateManager\Model\DateSchedule\GetList as DateScheduleGetList;
use Amasty\DeliveryDateManager\Model\Relations\DateScheduleChannelRelation\RelationProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;

class Duplicate
{
    /**
     * @var Get
     */
    private $channelGetter;

    /**
     * @var Save
     */
    private $channelSaver;

    /**
     * @var DateScheduleGetList
     */
    private $dateScheduleGetList;

    /**
     * @var DeliveryChannelDataFactory
     */
    private $channelFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RelationProvider
     */
    private $dateScheduleChannelRelationProvider;

    public function __construct(
        Get $channelGetter,
        Save $channelSaver,
        DateScheduleGetList $dateScheduleGetList,
        DeliveryChannelDataFactory $channelFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RelationProvider $dateScheduleChannelRelationProvider
    ) {
        $this->channelGetter = $channelGetter;
        $this->channelSaver = $channelSaver;
        $this->dateScheduleGetList = $dateScheduleGetList;
        $this->channelFactory = $channelFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateScheduleChannelRelationProvider = $dateScheduleChannelRelationProvider;
    }

    /**
     * @param int $channelId
     * @return DeliveryChannelInterface
     */
    public function execute(int $channelId): DeliveryChannelInterface
    {
        $dateExceptionIds = $this->getExceptionIds($channelId);
        /** @var DeliveryChannelData $mainChannel */
        $mainChannel = $this->channelGetter->execute($channelId);

        /** @var DeliveryChannelData $newChannel */
        $newChannel = $this->channelFactory->create();
        $newChannel->setData($mainChannel->getData());
        $newChannel->setChannelId(null);
        $newChannel->setIsActive(false);
        $newChannel->setName('Copy of ' . $mainChannel->getName());
        // Date Schedules/Exceptions Will Be Save in "AfterSave" Method of "DateScheduleChannelRelation" Data Handler
        $newChannel->setData(DateScheduleChannelRelation::SCHEDULE_IDS_KEY, $dateExceptionIds);

        return $this->channelSaver->execute($newChannel);
    }

    /**
     * @param int $channelId
     * @return array
     */
    private function getExceptionIds(int $channelId): array
    {
        $dateExceptionIds = [];
        $dateChannelLinks = $this->dateScheduleChannelRelationProvider->getListByChannelIds([$channelId]);
        $scheduleIds = $dateChannelLinks->getDateScheduleIds();

        $criteria = $this->prepareSearchCriteria($scheduleIds);
        $dateExceptionItems = $this->dateScheduleGetList->execute($criteria);
        foreach ($dateExceptionItems->getItems() as $exception) {
            $dateExceptionIds[] = $exception->getScheduleId();
        }

        return $dateExceptionIds;
    }

    /**
     * @param array $scheduleIds
     * @return SearchCriteriaInterface
     */
    private function prepareSearchCriteria(array $scheduleIds): SearchCriteriaInterface
    {
        $this->searchCriteriaBuilder->addFilter(DateScheduleInterface::SCHEDULE_ID, $scheduleIds, 'in');
        $this->searchCriteriaBuilder->addFilter(DateScheduleInterface::IS_AVAILABLE, 0);

        return $this->searchCriteriaBuilder->create();
    }
}
