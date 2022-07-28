<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Deliverydate;

interface LayoutProcessorInterface
{
    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process(array $jsLayout): array;
}
