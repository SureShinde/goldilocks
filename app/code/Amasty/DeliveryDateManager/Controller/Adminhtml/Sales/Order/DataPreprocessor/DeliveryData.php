<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Sales\Order\DataPreprocessor;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\DeliveryDate\DateFormatProvider;
use Amasty\DeliveryDateManager\Model\Preprocessor\PreprocessorInterface;
use Amasty\DeliveryDateManager\Model\TimeInterval\TimeToMinsConverter;
use Magento\Framework\Stdlib\DateTime;

class DeliveryData implements PreprocessorInterface
{
    /**
     * @var TimeToMinsConverter
     */
    private $timeToMinsConverter;

    /**
     * @var DateFormatProvider
     */
    private $dateFormatProvider;

    public function __construct(
        TimeToMinsConverter $timeToMinsConverter,
        DateFormatProvider $dateFormatProvider
    ) {
        $this->timeToMinsConverter = $timeToMinsConverter;
        $this->dateFormatProvider = $dateFormatProvider;
    }

    /**
     * @param array &$data
     */
    public function process(array &$data): void
    {
        if (!empty($data[DeliveryDateOrderInterface::DATE])) {
            $format = $this->dateFormatProvider->getDateFormat();
            $dateTime = \DateTime::createFromFormat(
                $this->dateFormatProvider->convert($format),
                $data[DeliveryDateOrderInterface::DATE]
            );

            if ($dateTime) {
                $data[DeliveryDateOrderInterface::DATE] = $dateTime->format(DateTime::DATE_PHP_FORMAT);
            }
        }

        if (!empty($data[DeliveryDateOrderInterface::TIME_FROM])) {
            $data[DeliveryDateOrderInterface::TIME_FROM]
                = $this->timeToMinsConverter->execute($data[DeliveryDateOrderInterface::TIME_FROM]);
        } else {
            $data[DeliveryDateOrderInterface::TIME_FROM] = null;
        }

        if (!empty($data[DeliveryDateOrderInterface::TIME_TO])) {
            $data[DeliveryDateOrderInterface::TIME_TO]
                = $this->timeToMinsConverter->execute($data[DeliveryDateOrderInterface::TIME_TO]);
        } else {
            $data[DeliveryDateOrderInterface::TIME_TO] = null;
        }
    }
}
