<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannel\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    public const ENABLED = 1;
    public const DISABLED = 0;

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::ENABLED,
                'label' => __('Enabled')
            ],
            [
                'value' => self::DISABLED,
                'label' => __('Disabled')
            ]
        ];
    }
}
