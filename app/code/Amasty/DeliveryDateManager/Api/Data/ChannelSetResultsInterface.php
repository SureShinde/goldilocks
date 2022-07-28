<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

/**
 * Delivery Channel Related data.
 * Usually processed a few delivery channels, and the channel have related date and time entities.
 * This class contain all related data.
 */
interface ChannelSetResultsInterface
{
    /**
     * Channel Set cache tag
     */
    public const CACHE_TAG = 'amdeliv_set';

    /**
     * @return \Amasty\DeliveryDateManager\Api\Data\DeliveryChannelSearchResultsInterface
     */
    public function getDeliveryChannel(): \Amasty\DeliveryDateManager\Api\Data\DeliveryChannelSearchResultsInterface;

    /**
     * @param \Amasty\DeliveryDateManager\Api\Data\DeliveryChannelSearchResultsInterface $deliveryChannel
     *
     * @return $this
     */
    public function setDeliveryChannel(
        \Amasty\DeliveryDateManager\Api\Data\DeliveryChannelSearchResultsInterface $deliveryChannel
    );

    /**
     * @return \Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface
     */
    public function getChannelConfig(): \Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface;

    /**
     * @param \Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface $configData
     *
     * @return $this
     */
    public function setChannelConfig(
        \Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface $configData
    );

    /**
     * @return \Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationSearchResultsInterface
     */
    public function getDateChannelLinks()
    : \Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationSearchResultsInterface;

    /**
     * @param \Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationSearchResultsInterface $dateChannelLinks
     *
     * @return $this
     */
    public function setDateChannelLinks(
        \Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationSearchResultsInterface $dateChannelLinks
    );

    /**
     * @return \Amasty\DeliveryDateManager\Api\Data\DateScheduleSearchResultsInterface
     */
    public function getDateScheduleItems(): \Amasty\DeliveryDateManager\Api\Data\DateScheduleSearchResultsInterface;

    /**
     * @param \Amasty\DeliveryDateManager\Api\Data\DateScheduleSearchResultsInterface $dateSchedule
     *
     * @return $this
     */
    public function setDateScheduleItems(
        \Amasty\DeliveryDateManager\Api\Data\DateScheduleSearchResultsInterface $dateSchedule
    );

    /**
     * @return \Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationSearchResultsInterface
     */
    public function getTimeChannelLinks()
    : \Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationSearchResultsInterface;

    /**
     * @param \Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationSearchResultsInterface $timeChannelLinks
     *
     * @return $this
     */
    public function setTimeChannelLinks(
        \Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationSearchResultsInterface $timeChannelLinks
    );

    /**
     * @return \Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationSearchResultsInterface
     */
    public function getTimeDateLinks()
    : \Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationSearchResultsInterface;

    /**
     * @param \Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationSearchResultsInterface $timeDateLinks
     *
     * @return $this
     */
    public function setTimeDateLinks(
        \Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationSearchResultsInterface $timeDateLinks
    );

    /**
     * @return \Amasty\DeliveryDateManager\Api\Data\TimeIntervalSearchResultsInterface
     */
    public function getTimeIntervalItems(): \Amasty\DeliveryDateManager\Api\Data\TimeIntervalSearchResultsInterface;

    /**
     * @param \Amasty\DeliveryDateManager\Api\Data\TimeIntervalSearchResultsInterface $timeIntervalItems
     *
     * @return $this
     */
    public function setTimeIntervalItems(
        \Amasty\DeliveryDateManager\Api\Data\TimeIntervalSearchResultsInterface $timeIntervalItems
    );
}
