<?php

namespace Acommerce\SmsIntegration\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CountryNumberCode implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            '+84' => __("Vietnam"),
            '+63' => __("Philippines")];
    }
}
