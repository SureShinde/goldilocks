<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Grid\Channel\Column\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;
use Magento\Shipping\Model\Config as ShippingConfig;

class ShippingMethod implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $htmlOptions;

    /**
     * @var ScopeConfigInterface
     */
    private $shippingConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        ShippingConfig $shippingConfig,
        ScopeConfigInterface $scopeConfig,
        Escaper $escaper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->shippingConfig = $shippingConfig;
        $this->escaper = $escaper;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->htmlOptions === null) {
            $this->htmlOptions = $this->getHtmlOptions();
        }

        return $this->htmlOptions;
    }

    /**
     * @return array
     */
    private function getHtmlOptions(): array
    {
        $htmlOptions = [
            [
                'value' => 0,
                'label' => __('All Shipping Methods')
            ]
        ];

        foreach ($this->shippingConfig->getActiveCarriers() as $carrierCode => $carrierModel) {
            if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                $carrierTitle = $this->scopeConfig->getValue('carriers/' . $carrierCode . '/title');
                foreach ($carrierMethods as $methodCode => $method) {
                    $value = $carrierCode . '_' . $methodCode;
                    $label = "<strong>". $carrierTitle . "</strong><br/>"
                        . str_repeat('&nbsp;', 4) . $this->escaper->escapeHtml($method) . "<br/>";
                    $htmlOptions[] = ['value' => $value, 'label' => $label];
                }
            }
        }

        return $htmlOptions;
    }
}
