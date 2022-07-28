<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Deliverydate\DataPreprocessor;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\Preprocessor\PreprocessorInterface;
use Amasty\DeliveryDateManager\Model\TimeInterval\Get;
use Magento\Framework\Escaper;

class DeliveryData implements PreprocessorInterface
{
    /**
     * @var Get
     */
    private $getTimeInterval;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(Get $getTimeInterval, Escaper $escaper)
    {
        $this->getTimeInterval = $getTimeInterval;
        $this->escaper = $escaper;
    }

    /**
     * @param array &$data
     */
    public function process(array &$data): void
    {
        $data[DeliveryDateOrderInterface::DATE] = null;
        $data[DeliveryDateOrderInterface::TIME_INTERVAL_ID] = null;
        $data[DeliveryDateOrderInterface::TIME_FROM] = null;
        $data[DeliveryDateOrderInterface::TIME_TO] = null;
        $data[DeliveryDateOrderInterface::COMMENT] = '';

        if (!empty($data['amdeliverydate_backend_date'])) {
            $data[DeliveryDateOrderInterface::DATE] = (string)$data['amdeliverydate_backend_date'];
        }

        if (!empty($data['amdeliverydate_time_id'])) {
            $newTimeIntervalId = (int)$data['amdeliverydate_time_id'];
            $timeInterval = $this->getTimeInterval->execute($newTimeIntervalId);
            $data[DeliveryDateOrderInterface::TIME_INTERVAL_ID] = $newTimeIntervalId;
            $data[DeliveryDateOrderInterface::TIME_FROM] = $timeInterval->getFrom();
            $data[DeliveryDateOrderInterface::TIME_TO] = $timeInterval->getTo();
        }

        if (!empty($data['amdeliverydate_comment'])) {
            $newComment = $this->escaper->escapeHtml((string)$data['amdeliverydate_comment']);
            $data[DeliveryDateOrderInterface::COMMENT] = $newComment;
        }
    }
}
