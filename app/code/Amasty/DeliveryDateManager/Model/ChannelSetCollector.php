<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model;

use Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface;
use Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterfaceFactory;
use Amasty\DeliveryDateManager\Api\Data\ChannelSetResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelSearchResultsInterface;
use Amasty\DeliveryDateManager\Model\DeliveryChannel\GetChannelByCurrentScopes;
use Amasty\DeliveryDateManager\Model\Relations\DateScheduleChannelRelation\RelationProvider
    as DateScheduleChannelRelationProvider;
use Amasty\DeliveryDateManager\Model\Relations\TimeIntervalChannelRelation\RelationProvider
    as TimeIntervalChannelRelationProvider;
use Amasty\DeliveryDateManager\Model\Relations\TimeIntervalDateScheduleRelation\RelationProvider
    as TimeIntervalDateScheduleRelationProvider;

/**
 * Class for Collect ChannelSet (Delivery Channels related data)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChannelSetCollector
{
    /**
     * @var ChannelConfig\Get
     */
    private $getChannelConfig;

    /**
     * @var DateScheduleChannelRelationProvider
     */
    private $dateScheduleChannelRelationProvider;

    /**
     * @var DateSchedule\Provider
     */
    private $dataScheduleProvider;

    /**
     * @var TimeIntervalChannelRelationProvider
     */
    private $timeIntervalChannelRelationProvider;

    /**
     * @var TimeIntervalDateScheduleRelationProvider
     */
    private $timeIntervalDateScheduleRelationProvider;

    /**
     * @var TimeInterval\Provider
     */
    private $timeIntervalProvider;

    /**
     * @var OrderLimit\Provider
     */
    private $orderLimitProvider;

    /**
     * @var ChannelSetResultsFactory
     */
    private $channelSetResultsFactory;

    /**
     * @var DeliveryChannel\GetChannelByCurrentScopes
     */
    private $getChannelByScopes;

    /**
     * @var ChannelConfigDataInterfaceFactory
     */
    private $channelConfigDataFactory;

    public function __construct(
        \Amasty\DeliveryDateManager\Model\ChannelConfig\Get $getChannelConfig,
        DateScheduleChannelRelationProvider $dateScheduleChannelRelationProvider,
        DateSchedule\Provider $dataScheduleProvider,
        TimeIntervalChannelRelationProvider $timeIntervalChannelRelationProvider,
        TimeIntervalDateScheduleRelationProvider $timeIntervalDateScheduleRelationProvider,
        TimeInterval\Provider $timeIntervalProvider,
        OrderLimit\Provider $orderLimitProvider,
        ChannelSetResultsFactory $channelSetResultsFactory,
        GetChannelByCurrentScopes $getChannelByScopes,
        ChannelConfigDataInterfaceFactory $channelConfigDataFactory
    ) {
        $this->getChannelConfig = $getChannelConfig;
        $this->dateScheduleChannelRelationProvider = $dateScheduleChannelRelationProvider;
        $this->dataScheduleProvider = $dataScheduleProvider;
        $this->timeIntervalChannelRelationProvider = $timeIntervalChannelRelationProvider;
        $this->timeIntervalDateScheduleRelationProvider = $timeIntervalDateScheduleRelationProvider;
        $this->timeIntervalProvider = $timeIntervalProvider;
        $this->orderLimitProvider = $orderLimitProvider;
        $this->channelSetResultsFactory = $channelSetResultsFactory;
        $this->getChannelByScopes = $getChannelByScopes;
        $this->channelConfigDataFactory = $channelConfigDataFactory;
    }

    /**
     * @return ChannelSetResults
     */
    public function collectChannelSet(): ChannelSetResults
    {
        $channelResult = $this->getChannelByScopes->execute();

        return $this->collectChannelSetByChannelSearchResult($channelResult);
    }

    /**
     * Load all related to Delivery Channel entities.
     *
     * @param DeliveryChannelSearchResultsInterface $channelSearchResult
     *
     * @return ChannelSetResults
     */
    public function collectChannelSetByChannelSearchResult(
        DeliveryChannelSearchResultsInterface $channelSearchResult
    ): ChannelSetResultsInterface {
        $channelIds = $channelSearchResult->getIds();
        $orderLimitIds = [];
        $channelConfigId = null;
        foreach ($channelSearchResult->getItems() as $item) {
            if ($item->getLimitId()) {
                $orderLimitIds[] = $item->getLimitId();
            }
            if ($channelConfigId === null && $item->getConfigId()) {
                $channelConfigId = $item->getConfigId();
            }
        }
        $dateChannelLinks = $this->dateScheduleChannelRelationProvider->getListByChannelIds($channelIds);
        $scheduleIds = $dateChannelLinks->getDateScheduleIds();
        $dateScheduleItems = $this->dataScheduleProvider->getScheduleByIds($scheduleIds);
        foreach ($dateScheduleItems->getItems() as $item) {
            if ($item->getLimitId()) {
                $orderLimitIds[] = $item->getLimitId();
            }
        }
        $timeChannelLinks = $this->timeIntervalChannelRelationProvider->getListByChannelIds($channelIds);
        $timeScheduleLinks = $this->timeIntervalDateScheduleRelationProvider->getListByDateScheduleIds($scheduleIds);
        $timeIds = array_merge($timeChannelLinks->getTimeIntervalIds(), $timeScheduleLinks->getTimeIntervalIds());
        $timeIntervalItems = $this->timeIntervalProvider->getAllowedListByIds($timeIds);
        foreach ($timeIntervalItems->getItems() as $item) {
            if ($item->getLimitId()) {
                $orderLimitIds[] = $item->getLimitId();
            }
        }
        $limitSearchResult = $this->orderLimitProvider->getOrderLimitByIds($orderLimitIds);

        if ($channelConfigId) {
            $channelConfig = $this->getChannelConfig->execute($channelConfigId);
        } else {
            /** @var ChannelConfigDataInterface $channelConfig */
            $channelConfig = $this->channelConfigDataFactory->create();
        }

        return $this->channelSetResultsFactory->create(
            [
                'deliveryChannel' => $channelSearchResult,
                'channelConfig' => $channelConfig,
                'dateChannelLinks' => $dateChannelLinks,
                'dateScheduleItems' => $dateScheduleItems,
                'timeChannelLinks' => $timeChannelLinks,
                'timeDateLinks' => $timeScheduleLinks,
                'timeIntervalItems' => $timeIntervalItems,
                'limitSearchResult' => $limitSearchResult
            ]
        );
    }
}
