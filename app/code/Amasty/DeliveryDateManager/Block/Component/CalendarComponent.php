<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Component;

use Amasty\DeliveryDateManager\Block\Checkout\LayoutProcessor;
use Amasty\DeliveryDateManager\Model\CheckoutConfigProvider;
use Amasty\DeliveryDateManager\Model\ConfigProvider;

class CalendarComponent implements ComponentInterface
{
    public const NAME = 'deliverydate_date';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CheckoutConfigProvider
     */
    private $checkoutConfig;

    public function __construct(
        ConfigProvider $configProvider,
        CheckoutConfigProvider $checkoutConfig
    ) {
        $this->configProvider = $configProvider;
        $this->checkoutConfig = $checkoutConfig;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getComponent(int $storeId): array
    {
        $element = [
            'component' => 'Amasty_DeliveryDateManager/js/view/date',
            'label' => __('Delivery Date'),
            'sortOrder' => 200,
            'disabled' => false,
            'additionalClasses' => 'date',
            'dataScope' => 'amdeliverydate_date',
            'provider' => 'checkoutProvider',
            'notice' => $this->configProvider->getDateNote($storeId),
            'visible' => true,
            'config' => [
                'template' => 'ui/form/field',
                'id' => 'delivery-date',
                'placeholder' => $this->configProvider->getDatePlaceholderText(),
                'storageConfig' => [
                    'provider' => 'sectionLocalStorage',
                    'namespace' => LayoutProcessor::STORAGE_SECTION_NAME . '.' . '${$.dataScope}'
                ],
                'statefull' => ['value' => true, 'shiftedValue' => true],
                'options' => [
                    //calendar lib options
                    'showOn' => 'both'
                ],
                'isRequired' => $this->configProvider->isDateRequired($storeId),
                'isTimeRequired' => $this->configProvider->isTimeEnabled($storeId)
                    && $this->configProvider->isTimeRequired($storeId),
                'readonly' => 1,
                'pickerDefaultDateFormat' => $this->checkoutConfig->getPickerDateFormat(),
                'pickerDateTimeFormat' => $this->checkoutConfig->getPickerDateFormat(),
                'outputDateFormat' => $this->checkoutConfig->getOutputDateFormat(),
                'inputDateFormat' => $this->checkoutConfig->getInputDateFormat(),
                'invalidDateMsg' => __(
                    'Selected date is not available for delivery. Please select another one.'
                )
            ]
        ];

        return $element;
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isEnabled(int $storeId): bool
    {
        return true;
    }
}
