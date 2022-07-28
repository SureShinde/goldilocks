<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class NoteState implements OptionSourceInterface
{
    public const HIDDEN = 0;
    public const REPLACED_WITH_DEFAULT = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::HIDDEN,
                'label' => __('Hidden')
            ],
            [
                'value' => self::REPLACED_WITH_DEFAULT,
                'label' => __('Replaced with default note')
            ]
        ];
    }
}
