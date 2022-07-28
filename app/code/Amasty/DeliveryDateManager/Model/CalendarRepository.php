<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model;

use Amasty\DeliveryDateManager\Model\OrderLimit\Restricted\RestrictedDateProvider;

class CalendarRepository
{
    /**
     * @var SearchResultToArray
     */
    private $resultToArray;

    /**
     * @var ChannelSetRepository
     */
    private $channelSetRepository;

    /**
     * @var RestrictedDateProvider
     */
    private $restrictedDateProvider;

    public function __construct(
        SearchResultToArray $resultToArray,
        ChannelSetRepository $channelSetRepository,
        OrderLimit\Restricted\RestrictedDateProvider $restrictedDateProvider
    ) {
        $this->resultToArray = $resultToArray;
        $this->channelSetRepository = $channelSetRepository;
        $this->restrictedDateProvider = $restrictedDateProvider;
    }

    /**
     * @return array
     */
    public function getCalendarSet(): array
    {
        $channelSet = $this->channelSetRepository->getByScope();

        $disabledDaysByLimit = $this->restrictedDateProvider->getRestrictedArrayByChannelSet($channelSet);

        return [
            'channel' => $this->resultToArray->getItems($channelSet->getDeliveryChannel()),
            'config' => $this->convertModelDataToArray($channelSet->getChannelConfig()),
            'dateScheduleItems' => $this->resultToArray->getItems($channelSet->getDateScheduleItems()),
            'timeIntervalItems' => $this->resultToArray->getItems($channelSet->getTimeIntervalItems()),
            'dateChannelLinks' => $this->resultToArray->getItems($channelSet->getDateChannelLinks()),
            'timeChannelLinks' => $this->resultToArray->getItems($channelSet->getTimeChannelLinks()),
            'timeScheduleLinks' => $this->resultToArray->getItems($channelSet->getTimeDateLinks()),
            'disabledDaysByLimit' => $disabledDaysByLimit,
        ];
    }

    /**
     * @param \Magento\Framework\DataObject $modelData
     *
     * @return array
     */
    private function convertModelDataToArray(\Magento\Framework\DataObject $modelData): array
    {
        $data = $modelData->toArray();
        foreach ($data as $key => &$value) {
            $value = $modelData->getDataUsingMethod($key);
        }

        return $data;
    }
}
