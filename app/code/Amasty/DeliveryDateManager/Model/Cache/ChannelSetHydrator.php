<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Cache;

use Amasty\DeliveryDateManager\Api\Data\ChannelSetResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationInterface;
use Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Api\Data\DateScheduleSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Api\Data\OrderLimitSearchResultInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalSearchResultsInterface;
use Amasty\DeliveryDateManager\Model\ChannelConfig\ConfigDataFactory;
use Amasty\DeliveryDateManager\Model\ChannelSetResults;
use Amasty\DeliveryDateManager\Model\ChannelSetResultsFactory;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Hydrator of ChannelSetResults
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChannelSetHydrator
{
    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var ChannelSetResultsFactory
     */
    private $channelSetResultsFactory;

    /**
     * @var ConfigDataFactory
     */
    private $configDataFactory;

    /**
     * @var ChannelSetHydrator\SearchResultHydrator
     */
    private $searchResultHydrator;

    public function __construct(
        DataObjectProcessor $dataObjectProcessor,
        ChannelSetResultsFactory $channelSetResultsFactory,
        ConfigDataFactory $configDataFactory,
        ChannelSetHydrator\SearchResultHydrator $searchResultHydrator
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->channelSetResultsFactory = $channelSetResultsFactory;
        $this->configDataFactory = $configDataFactory;
        $this->searchResultHydrator = $searchResultHydrator;
    }

    /**
     * @param ChannelSetResultsInterface|ChannelSetResults $dataObject
     *
     * @return array
     */
    public function extract(ChannelSetResultsInterface $dataObject): array
    {
        $data = $this->dataObjectProcessor->buildOutputDataArray($dataObject, ChannelSetResultsInterface::class);
        $data['limit_search_result'] = $this->dataObjectProcessor->buildOutputDataArray(
            $dataObject->getLimitSearchResult(),
            OrderLimitSearchResultInterface::class
        );

        return $data;
    }

    /**
     * Create Channel Set object by array
     *
     * @param array $data
     *
     * @return ChannelSetResults
     */
    public function hydrate(array $data): ChannelSetResults
    {
        return $this->channelSetResultsFactory->create(
            [
                'deliveryChannel' => $this->hydrateChannelResult($data),
                'channelConfig' => $this->configDataFactory->create(['data' => $data['channel_config']]),
                'dateChannelLinks' => $this->hydrateDateChannelResult($data),
                'dateScheduleItems' => $this->hydrateDateResult($data),
                'timeChannelLinks' => $this->hydrateTimeChannelResult($data),
                'timeDateLinks' => $this->hydrateTimeDateResult($data),
                'timeIntervalItems' => $this->hydrateTimeResult($data),
                'limitSearchResult' => $this->hydrateLimitResult($data)
            ]
        );
    }

    private function hydrateChannelResult(array $data): SearchResultsInterface
    {
        return $this->searchResultHydrator->hydrateSearchResult(
            $data['delivery_channel'],
            DeliveryChannelSearchResultsInterface::class,
            DeliveryChannelInterface::class
        );
    }

    private function hydrateDateChannelResult(array $data): SearchResultsInterface
    {
        return $this->searchResultHydrator->hydrateSearchResult(
            $data['date_channel_links'],
            DateScheduleChannelRelationSearchResultsInterface::class,
            DateScheduleChannelRelationInterface::class
        );
    }

    private function hydrateDateResult(array $data): SearchResultsInterface
    {
        return $this->searchResultHydrator->hydrateSearchResult(
            $data['date_schedule_items'],
            DateScheduleSearchResultsInterface::class,
            DateScheduleInterface::class
        );
    }

    private function hydrateTimeChannelResult(array $data): SearchResultsInterface
    {
        return $this->searchResultHydrator->hydrateSearchResult(
            $data['time_channel_links'],
            TimeIntervalChannelRelationSearchResultsInterface::class,
            TimeIntervalChannelRelationInterface::class
        );
    }

    private function hydrateTimeDateResult(array $data): SearchResultsInterface
    {
        return $this->searchResultHydrator->hydrateSearchResult(
            $data['time_date_links'],
            TimeIntervalDateScheduleRelationSearchResultsInterface::class,
            TimeIntervalDateScheduleRelationInterface::class
        );
    }

    private function hydrateTimeResult(array $data): SearchResultsInterface
    {
        return $this->searchResultHydrator->hydrateSearchResult(
            $data['time_interval_items'],
            TimeIntervalSearchResultsInterface::class,
            TimeIntervalInterface::class
        );
    }

    private function hydrateLimitResult(array $data): SearchResultsInterface
    {
        return $this->searchResultHydrator->hydrateSearchResult(
            $data['limit_search_result'],
            OrderLimitSearchResultInterface::class,
            OrderLimitInterface::class
        );
    }
}
