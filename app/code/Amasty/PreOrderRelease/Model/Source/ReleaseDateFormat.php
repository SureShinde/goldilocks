<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class ReleaseDateFormat implements OptionSourceInterface
{
    public const DEFAULT_FORMAT = 'default';

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * Patterns getting from http://userguide.icu-project.org/formatparse/datetime
     * for IntlDateFormatter::format
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'default',
                'label' => __('Magento Default')
            ],
            [
                'value' => 'MMMM dd, y',
                'label' => 'F d, Y (' . $this->dateTime->date('F d, Y') . ')'
            ],
            [
                'value' => 'MMM dd, y',
                'label' => 'M d, Y (' . $this->dateTime->date('M d, Y') . ')'
            ],
            [
                'value' => 'y-MM-dd',
                'label' => 'Y-m-d (' . $this->dateTime->date('Y-m-d') . ')'
            ],
            [
                'value' => 'MM/dd/y',
                'label' => 'm/d/Y (' . $this->dateTime->date('m/d/Y') . ')'
            ],
            [
                'value' => 'dd/MM/y',
                'label' => 'd/m/Y (' . $this->dateTime->date('d/m/Y') . ')'
            ],
            [
                'value' => 'd/M/yy',
                'label' => 'j/n/y (' . $this->dateTime->date('j/n/y') . ')'
            ],
            [
                'value' => 'd/M/y',
                'label' => 'j/n/Y (' . $this->dateTime->date('j/n/Y') . ')'
            ],
            [
                'value' => 'dd.MM.y',
                'label' => 'd.m.Y (' . $this->dateTime->date('d.m.Y') . ')'
            ],
            [
                'value' => 'dd.MM.yy',
                'label' => 'd.m.y (' . $this->dateTime->date('d.m.y') . ')'
            ],
            [
                'value' => 'd.M.yy',
                'label' => 'j.n.y (' . $this->dateTime->date('j.n.y') . ')'
            ],
            [
                'value' => 'd.M.y',
                'label' => 'j.n.Y (' . $this->dateTime->date('j.n.Y') . ')'
            ],
            [
                'value' => 'd-M-yy',
                'label' => 'd-m-y (' . $this->dateTime->date('d-m-y') . ')'
            ],
            [
                'value' => 'y.MM.dd',
                'label' => 'Y.m.d (' . $this->dateTime->date('Y.m.d') . ')'
            ],
            [
                'value' => 'dd-MM-y',
                'label' => 'd-m-Y (' . $this->dateTime->date('d-m-Y') . ')'
            ],
            [
                'value' => 'y/MM/dd',
                'label' => 'Y/m/d (' . $this->dateTime->date('Y/m/d') . ')'
            ],
            [
                'value' => 'yy/MM/dd',
                'label' => 'y/m/d (' . $this->dateTime->date('y/m/d') . ')'
            ],
            [
                'value' => 'dd/MM/yy',
                'label' => 'd/m/y (' . $this->dateTime->date('d/m/y') . ')'
            ],
            [
                'value' => 'MM/dd/yy',
                'label' => 'm/d/y (' . $this->dateTime->date('m/d/y') . ')'
            ],
            [
                'value' => 'dd/MM y',
                'label' => 'd/m Y (' . $this->dateTime->date('d/m Y') . ')'
            ],
            [
                'value' => 'y MM dd',
                'label' => 'Y m d (' . $this->dateTime->date('Y m d') . ')'
            ]
        ];
    }
}
