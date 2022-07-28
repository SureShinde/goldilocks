<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model;

use Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface;
use Amasty\DeliveryDateManager\Api\Data\ChannelConfigSearchResultInterface;
use Amasty\DeliveryDateManager\Api\Data\ChannelSetResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\DateScheduleSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\OrderLimitSearchResultInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalSearchResultsInterface;

/**
 * Delivery Channel Related data.
 * Usually processed a few delivery channels, and the channel have related date and time entities.
 * This class contain all related data.
 */
class ChannelSetResults implements ChannelSetResultsInterface
{
    /**
     * @var DeliveryChannelSearchResultsInterface
     */
    private $deliveryChannel;

    /**
     * @var ChannelConfigDataInterface
     */
    private $channelConfig;

    /**
     * @var DateScheduleChannelRelationSearchResultsInterface
     */
    private $dateChannelLinks;

    /**
     * @var DateScheduleSearchResultsInterface
     */
    private $dateScheduleItems;

    /**
     * @var TimeIntervalChannelRelationSearchResultsInterface
     */
    private $timeChannelLinks;

    /**
     * @var TimeIntervalDateScheduleRelationSearchResultsInterface
     */
    private $timeDateLinks;

    /**
     * @var TimeIntervalSearchResultsInterface
     */
    private $timeIntervalItems;

    /**
     * @var OrderLimitSearchResultInterface
     */
    private $limitSearchResult;

    public function __construct(
        DeliveryChannelSearchResultsInterface $deliveryChannel,
        ChannelConfigDataInterface $channelConfig,
        DateScheduleChannelRelationSearchResultsInterface $dateChannelLinks,
        DateScheduleSearchResultsInterface $dateScheduleItems,
        TimeIntervalChannelRelationSearchResultsInterface $timeChannelLinks,
        TimeIntervalDateScheduleRelationSearchResultsInterface $timeDateLinks,
        TimeIntervalSearchResultsInterface $timeIntervalItems,
        OrderLimitSearchResultInterface $limitSearchResult
    ) {
        $this->deliveryChannel = $deliveryChannel;
        $this->channelConfig = $channelConfig;
        $this->dateChannelLinks = $dateChannelLinks;
        $this->dateScheduleItems = $dateScheduleItems;
        $this->timeChannelLinks = $timeChannelLinks;
        $this->timeDateLinks = $timeDateLinks;
        $this->timeIntervalItems = $timeIntervalItems;
        $this->limitSearchResult = $limitSearchResult;
    }

    /**
     * @return DeliveryChannelSearchResultsInterface
     */
    public function getDeliveryChannel(): DeliveryChannelSearchResultsInterface
    {
        return $this->deliveryChannel;
    }

    /**
     * @param DeliveryChannelSearchResultsInterface $deliveryChannel
     *
     * @return $this
     */
    public function setDeliveryChannel(DeliveryChannelSearchResultsInterface $deliveryChannel)
    {
        $this->deliveryChannel = $deliveryChannel;

        return $this;
    }

    /**
     * @return ChannelConfigDataInterface
     */
    public function getChannelConfig(): ChannelConfigDataInterface
    {
        return $this->channelConfig;
    }

    /**
     * @param ChannelConfigDataInterface $configData
     *
     * @return $this
     */
    public function setChannelConfig(ChannelConfigDataInterface $configData)
    {
        $this->channelConfig = $configData;

        return $this;
    }

    /**
     * @return DateScheduleChannelRelationSearchResultsInterface
     */
    public function getDateChannelLinks(): DateScheduleChannelRelationSearchResultsInterface
    {
        return $this->dateChannelLinks;
    }

    /**
     * @param DateScheduleChannelRelationSearchResultsInterface $dateChannelLinks
     *
     * @return $this
     */
    public function setDateChannelLinks(DateScheduleChannelRelationSearchResultsInterface $dateChannelLinks)
    {
        $this->dateChannelLinks = $dateChannelLinks;

        return $this;
    }

    /**
     * @return DateScheduleSearchResultsInterface
     */
    public function getDateScheduleItems(): DateScheduleSearchResultsInterface
    {
        return $this->dateScheduleItems;
    }

    /**
     * @param DateScheduleSearchResultsInterface $dateScheduleItems
     *
     * @return $this
     */
    public function setDateScheduleItems(DateScheduleSearchResultsInterface $dateScheduleItems)
    {
        $this->dateScheduleItems = $dateScheduleItems;

        return $this;
    }

    /**
     * @return TimeIntervalChannelRelationSearchResultsInterface
     */
    public function getTimeChannelLinks(): TimeIntervalChannelRelationSearchResultsInterface
    {
        return $this->timeChannelLinks;
    }

    /**
     * @param TimeIntervalChannelRelationSearchResultsInterface $timeChannelLinks
     *
     * @return $this
     */
    public function setTimeChannelLinks(TimeIntervalChannelRelationSearchResultsInterface $timeChannelLinks)
    {
        $this->timeChannelLinks = $timeChannelLinks;

        return $this;
    }

    /**
     * @return TimeIntervalDateScheduleRelationSearchResultsInterface
     */
    public function getTimeDateLinks(): TimeIntervalDateScheduleRelationSearchResultsInterface
    {
        return $this->timeDateLinks;
    }

    /**
     * @param TimeIntervalDateScheduleRelationSearchResultsInterface $timeDateLinks
     *
     * @return $this
     */
    public function setTimeDateLinks(TimeIntervalDateScheduleRelationSearchResultsInterface $timeDateLinks)
    {
        $this->timeDateLinks = $timeDateLinks;

        return $this;
    }

    /**
     * @return TimeIntervalSearchResultsInterface
     */
    public function getTimeIntervalItems(): TimeIntervalSearchResultsInterface
    {
        return $this->timeIntervalItems;
    }

    /**
     * @param TimeIntervalSearchResultsInterface $timeIntervalItems
     *
     * @return $this
     */
    public function setTimeIntervalItems(TimeIntervalSearchResultsInterface $timeIntervalItems)
    {
        $this->timeIntervalItems = $timeIntervalItems;

        return $this;
    }

    /**
     * @return OrderLimitSearchResultInterface
     */
    public function getLimitSearchResult(): OrderLimitSearchResultInterface
    {
        return $this->limitSearchResult;
    }

    /**
     * @param OrderLimitSearchResultInterface $limitSearchResult
     *
     * @return $this
     */
    public function setLimitSearchResult(
        OrderLimitSearchResultInterface $limitSearchResult
    ) {
        $this->limitSearchResult = $limitSearchResult;

        return $this;
    }
}
