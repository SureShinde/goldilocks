<?php

namespace Amasty\DeliveryDateManager\Model\Config\Source;

class IncludeInto implements \Magento\Framework\Data\OptionSourceInterface
{
    public const ORDER_PRINT = 'order_print';
    public const ORDER_EMAIL = 'order_email';
    public const INVOICE_EMAIL = 'invoice_email';
    public const SHIPMENT_EMAIL = 'shipment_email';
    public const INVOICE_PDF = 'invoice_pdf';
    public const SHIPMENT_PDF = 'shipment_pdf';

    public function toOptionArray()
    {
        return [
            [
                'value' => self::ORDER_PRINT,
                'label' => __('Print Copy of Order Confirmation')
            ],
            [
                'value' => self::ORDER_EMAIL,
                'label' => __('Order Confirmation E-mail')
            ],
            [
                'value' => self::INVOICE_EMAIL,
                'label' => __('Invoice E-mail')
            ],
            [
                'value' => self::SHIPMENT_EMAIL,
                'label' => __('Shipment E-mail')
            ],
            [
                'value' => self::INVOICE_PDF,
                'label' => __('Invoice PDF')
            ],
            [
                'value' => self::SHIPMENT_PDF,
                'label' => __('Shipment PDF (Packing Slip)')
            ],
        ];
    }
}
