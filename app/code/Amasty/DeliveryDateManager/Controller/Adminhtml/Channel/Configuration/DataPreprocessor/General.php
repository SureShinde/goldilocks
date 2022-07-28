<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\Configuration\DataPreprocessor;

use Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface;
use Amasty\DeliveryDateManager\Model\Preprocessor\PreprocessorInterface;
use Amasty\DeliveryDateManager\Model\TimeInterval\TimeToMinsConverter;

class General implements PreprocessorInterface
{
    /**
     * @var TimeToMinsConverter
     */
    private $timeToMinsConverter;

    public function __construct(TimeToMinsConverter $timeToMinsConverter)
    {
        $this->timeToMinsConverter = $timeToMinsConverter;
    }

    /**
     * @param array &$data
     */
    public function process(array &$data): void
    {
        if (empty($data)) {
            return;
        }

        $isSameDayAvailable = $data[ChannelConfigDataInterface::IS_SAME_DAY_AVAILABLE] ?? false;
        $cutoffTime = $data[ChannelConfigDataInterface::SAME_DAY_CUTOFF] ?? null;

        if ($isSameDayAvailable) {
            if ($cutoffTime) {
                $data[ChannelConfigDataInterface::SAME_DAY_CUTOFF] = $this->timeToMinsConverter
                    ->execute($cutoffTime);
            }
        } else {
            $data[ChannelConfigDataInterface::SAME_DAY_CUTOFF] = null;
            $data[ChannelConfigDataInterface::ORDER_TIME] = null;
            $data[ChannelConfigDataInterface::BACKORDER_TIME] = null;
        }
    }
}
