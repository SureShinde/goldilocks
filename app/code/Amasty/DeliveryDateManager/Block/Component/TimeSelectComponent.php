<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Component;

use Amasty\DeliveryDateManager\Block\Checkout\LayoutProcessor;
use Amasty\DeliveryDateManager\Model\ConfigProvider;

class TimeSelectComponent implements ComponentInterface
{
    public const NAME = 'deliverydate_time';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
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
        return [
            'component' => 'Amasty_DeliveryDateManager/js/view/time-select',
            'label' => __('Delivery Time Interval'),
            'sortOrder' => 201,
            'disabled' => false,
            'isRequired' => $this->configProvider->isTimeRequired($storeId),
            'dataScope' => 'amdeliverydate_time_id',
            'notice' => $this->configProvider->getTimeNote(),
            'visible' => true,
            'provider' => 'checkoutProvider',
            'config' => [
                'template' => 'ui/form/field',
                'caption' => $this->configProvider->getTimePlaceholderText() ?? ' ',
                'storageConfig' => [
                    'provider' => 'sectionLocalStorage',
                    'namespace' => LayoutProcessor::STORAGE_SECTION_NAME  . '.' . '${$.dataScope}'
                ],
                'statefull' => ['value' => true],
                'filterBy' => [
                    'target' => '${$.parentName}.' . CalendarComponent::NAME . ':value'
                ],
            ]
        ];
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isEnabled(int $storeId): bool
    {
        return $this->configProvider->isTimeEnabled($storeId);
    }
}
