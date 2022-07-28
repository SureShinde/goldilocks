<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\Source;

use Magento\CatalogInventory\Model\Source\Backorders as BackordersSource;
use Magento\Framework\Data\OptionSourceInterface;

class ChangeBackorders implements OptionSourceInterface
{
    public const NO = 102;

    /**
     * @var BackordersSource
     */
    private $backordersSource;

    public function __construct(BackordersSource $backordersSource)
    {
        $this->backordersSource = $backordersSource;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $backordersOptions = $this->backordersSource->toOptionArray();
        array_unshift($backordersOptions, [
            'value' => self::NO,
            'label' => __('No')
        ]);

        return $backordersOptions;
    }
}
