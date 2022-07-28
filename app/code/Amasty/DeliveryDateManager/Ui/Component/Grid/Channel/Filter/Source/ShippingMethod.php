<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Grid\Channel\Filter\Source;

use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;
use Magento\Shipping\Model\Config as ShippingConfig;

class ShippingMethod implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

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

    /**
     * @var DeliveryChannel
     */
    private $deliveryChannelResource;

    public function __construct(
        ShippingConfig $shippingConfig,
        ScopeConfigInterface $scopeConfig,
        Escaper $escaper,
        DeliveryChannel $deliveryChannelResource
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->shippingConfig = $shippingConfig;
        $this->escaper = $escaper;
        $this->deliveryChannelResource = $deliveryChannelResource;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $this->options = $this->getOptions();
        }

        return $this->options;
    }

    /**
     * @return array
     */
    private function getOptions(): array
    {
        $options = [
            [
                'value' => 0,
                'label' => __('All Methods')
            ]
        ];
        $channelShippingMethods = $this->deliveryChannelResource->getAllShippingMethods();

        foreach ($this->shippingConfig->getAllCarriers() as $carrierCode => $carrierModel) {
            if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                $carrierTitle = $this->scopeConfig->getValue('carriers/' . $carrierCode . '/title');
                foreach ($carrierMethods as $methodCode => $method) {
                    $value = $carrierCode . '_' . $methodCode;

                    if (in_array($value, $channelShippingMethods)) {
                        $label = $carrierTitle . ' - ' . $this->escaper->escapeHtml($method);
                        $options[] = [
                            'value' => $value,
                            'label' => $label
                        ];
                    }
                }
            }
        }

        return $options;
    }
}
