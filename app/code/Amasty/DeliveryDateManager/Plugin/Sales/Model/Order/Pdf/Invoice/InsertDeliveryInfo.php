<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Plugin\Sales\Model\Order\Pdf\Invoice;

use Amasty\DeliveryDateManager\Model\Config\Source\IncludeInto;
use Amasty\DeliveryDateManager\Model\DDPdfCollector;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Pdf\AbstractPdf;

/**
 * Insert Delivery Date information Block to Invoice Admin PDF
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
     * @param Invoice[] $invoices
     * @return array
     */
    public function beforeGetPdf(AbstractPdf $subject, $invoices = []): array
    {
        return $this->pdfCollector->setEntityObject($invoices);
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
            IncludeInto::INVOICE_PDF,
            __('Invoice # ')
        );
    }
}
