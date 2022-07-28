<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\Schedule\DataPreprocessor;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Model\DateSchedule\Type\ConvertToStorable;
use Amasty\DeliveryDateManager\Model\Preprocessor\PreprocessorInterface;

class General implements PreprocessorInterface
{
    /**
     * @var ConvertToStorable
     */
    private $convertToStorable;

    public function __construct(ConvertToStorable $convertToStorable)
    {
        $this->convertToStorable = $convertToStorable;
    }

    /**
     * @param array &$data
     */
    public function process(array &$data): void
    {
        if (empty($data)) {
            return;
        }

        $type = (int)$data[DateScheduleInterface::TYPE];
        $from = $data[DateScheduleInterface::FROM . '_' . $type] ?? null;
        $to =  $data[DateScheduleInterface::TO . '_' . $type] ?? null;

        if ($from && $to) {
            $data[DateScheduleInterface::FROM] = $this->convertToStorable->execute($type, $from);
            $data[DateScheduleInterface::TO] = $this->convertToStorable->execute($type, $to);
        }
    }
}
