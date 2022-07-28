<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\ConfigDisplay;

class InfoOutput
{
    /**
     * @var OutputFormatter
     */
    private $outputFormatter;

    /**
     * @var ConfigDisplay
     */
    private $configDisplay;

    public function __construct(OutputFormatter $outputFormatter, ConfigDisplay $configDisplay)
    {
        $this->outputFormatter = $outputFormatter;
        $this->configDisplay = $configDisplay;
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDate
     * @param string $place
     * @param int|null $storeId
     * @return array array(array('code' => 'string', 'label' => 'string', 'value' => 'string'), ...)
     */
    public function getOutput(DeliveryDateOrderInterface $deliveryDate, string $place, int $storeId = null): array
    {
        $list = [];
        if ($this->configDisplay->isDateDisplayOn($place, $storeId) && $deliveryDate->getDate()) {
            $list[ConfigDisplay::DATE] = [
                'label' => __('Delivery Date') . ':',
                'value' => $this->outputFormatter->getFormattedDateFromDeliveryOrder($deliveryDate)
            ];
        }
        if ($this->configDisplay->isTimeDisplayOn($place, $storeId)
            && ($deliveryDate->getTimeIntervalId() || $deliveryDate->getTimeFrom() || $deliveryDate->getTimeTo())
        ) {
            $list[ConfigDisplay::TIME] = [
                'label' => __('Delivery Time Interval') . ':',
                'value' => $this->outputFormatter->getTimeLabelFromDeliveryOrder($deliveryDate)
            ];
        }

        if ($this->configDisplay->isCommentDisplayOn($place, $storeId) && $deliveryDate->getComment()) {
            $list[ConfigDisplay::COMMENT] = [
                'label' => __('Delivery Comments') . ':',
                'value' => $this->outputFormatter->getComment($deliveryDate)
            ];
        }

        return $list;
    }
}
