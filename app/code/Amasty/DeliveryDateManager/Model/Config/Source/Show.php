<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Show implements OptionSourceInterface
{
    public const ORDER_VIEW = 'order_view';
    public const ORDER_CREATE = 'order_create';
    public const INVOICE_VIEW = 'invoice_view';
    public const SHIPMENT_VIEW = 'shipment_view';
    public const ORDER_INFO = 'order_info';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::ORDER_VIEW,
                'label' => __('Order View Page (Backend)')
            ],
            [
                'value' => self::ORDER_CREATE,
                'label' => __('New/Edit/Reorder Order Page (Backend)')
            ],
            [
                'value' => self::INVOICE_VIEW,
                'label' => __('Invoice View Page (Backend)')
            ],
            [
                'value' => self::SHIPMENT_VIEW,
                'label' => __('Shipment View Page (Backend)')
            ],
            [
                'value' => self::ORDER_INFO,
                'label' => __('Order Info Page (Frontend)')
            ],
        ];
    }
}
