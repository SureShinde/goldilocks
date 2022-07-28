<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Grid\Channel\Filter\Source\Schedule;

use Magento\Framework\Data\OptionSourceInterface;

class Behavior implements OptionSourceInterface
{
    public const ALLOW = 1;
    public const DISALLOW = 0;

    /**
     * @var array
     */
    private $options;

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $this->options = [
                [
                    'value' => self::ALLOW,
                    'label' => __('Allow delivery')
                ],
                [
                    'value' => self::DISALLOW,
                    'label' => __('Prohibit delivery')
                ]
            ];
        }

        return $this->options;
    }
}
