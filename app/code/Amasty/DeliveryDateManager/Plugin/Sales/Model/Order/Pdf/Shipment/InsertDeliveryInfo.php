<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Plugin\Sales\Model\Order\Pdf\Shipment;

use Amasty\DeliveryDateManager\Model\Config\Source\IncludeInto;
use Amasty\DeliveryDateManager\Model\DDPdfCollector;
use Magento\Sales\Model\Order\Pdf\AbstractPdf;
use Magento\Sales\Model\Order\Shipment;

/**
 * Insert Delivery Date information Block to Shipment Admin PDF
 */
class InsertDeliveryInfo
{
    /**
     * @var DDPdfCollector
     */
    private $pdfCollector;

    public function __construct(DDPdfCollector $pdfCollector) {
        $this->pdfCollector = $pdfCollector;
    }

    /**
     * @param AbstractPdf $subject
     * @param Shipment[] $shipments
     * @return array
     */
    public function beforeGetPdf(AbstractPdf $subject, $shipments = []): array
    {
        return $this->pdfCollector->setEntityObject($shipments);
    }

    /**
     * @param AbstractPdf $subject
     * @param \Zend_Pdf_Page $page
     * @param string $text
     * @return array
     */
    public function beforeInsertDocumentNumber(AbstractPdf $subject, \Zend_Pdf_Page $page, string $text): array
    {
        return $this->pdfCollector->drawDeliveryInformation(
            $subject,
            $page,
            $text,
            IncludeInto::SHIPMENT_PDF,
            __('Packing Slip # ')
        );
    }
}
