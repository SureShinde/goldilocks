<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Get;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\OutputFormatter;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Pdf\AbstractPdf;
use Magento\Sales\Model\Order\Shipment;

/**
 * Insert Delivery Date information Block to PDF
 */
class DDPdfCollector
{
    /**
     * @var Shipment[]|Invoice[]
     */
    private $objects = [];

    /**
     * @var Get
     */
    private $dateRepository;

    /**
     * @var OutputFormatter
     */
    private $outputFormatter;

    /**
     * @var ConfigDisplay
     */
    private $configDisplay;

    public function __construct(
        Get $dateRepository,
        OutputFormatter $outputFormatter,
        ConfigDisplay $configDisplay
    ) {
        $this->dateRepository = $dateRepository;
        $this->outputFormatter = $outputFormatter;
        $this->configDisplay = $configDisplay;
    }

    /**
     * @param Shipment[]|Invoice[] $objects
     *
     * @return array
     */
    public function setEntityObject($objects = []): array
    {
        $this->objects = $objects;
        return [$objects];
    }

    /**
     * @param AbstractPdf $subject
     * @param \Zend_Pdf_Page $page
     * @param string $text
     * @param string $place
     * @param Phrase|string $phrasePrefix
     * @return array
     */
    public function drawDeliveryInformation(
        AbstractPdf $subject,
        \Zend_Pdf_Page $page,
        string $text,
        string $place,
        $phrasePrefix = ''
    ): array {
        $order = $this->getCurrentOrder($text, $phrasePrefix);
        if (!$order
            || (!$this->configDisplay->isDateDisplayOn($place)
                && !$this->configDisplay->isTimeDisplayOn($place)
                && !$this->configDisplay->isCommentDisplayOn($place))
        ) {
            return [$page, $text];
        }
        $deliveryDate = $this->getCurrentDeliveryDate($order);
        if (!$deliveryDate) {
            return [$page, $text];
        }

        $this->drawDeliveryInfoHeader($subject, $page);
        $this->drawDeliveryInfoContent($subject, $page, $deliveryDate, $place);

        return [$page, $text];
    }

    /**
     * Paste block title to PDF
     *
     * @param AbstractPdf $subject
     * @param \Zend_Pdf_Page $page
     */
    private function drawDeliveryInfoHeader(AbstractPdf $subject, \Zend_Pdf_Page $page): void
    {
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $subject->y, 570, $subject->y - 15);
        $subject->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Delivery Information'), 'feed' => 35];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $subject->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $subject->y -= 20;
    }

    /**
     * Paste block content to PDF
     *
     * @param AbstractPdf $subject
     * @param \Zend_Pdf_Page $page
     * @param DeliveryDateOrderInterface $deliveryDate
     * @param string $place
     */
    private function drawDeliveryInfoContent(
        AbstractPdf $subject,
        \Zend_Pdf_Page $page,
        DeliveryDateOrderInterface $deliveryDate,
        string $place
    ): void {
        if ($deliveryDate->getDate() && $this->configDisplay->isDateDisplayOn($place)) {
            $formattedDate = $this->outputFormatter->getFormattedDateFromDeliveryOrder($deliveryDate);
            $page->drawText(__('Delivery Date') . ': ' . $formattedDate, 35, $subject->y, 'UTF-8');
            $subject->y -= 15;
        }
        if ($deliveryDate->getTimeFrom() && $this->configDisplay->isTimeDisplayOn($place)) {
            $formattedTime = $this->outputFormatter->getTimeLabelFromDeliveryOrder($deliveryDate);
            $page->drawText(__('Delivery Time') . ': ' . $formattedTime, 35, $subject->y, 'UTF-8');
            $subject->y -= 15;
        }
        if ($deliveryDate->getComment() && $this->configDisplay->isCommentDisplayOn($place)) {
            $commentLines = explode("\n", wordwrap($deliveryDate->getComment(), 127));
            $page->drawText(__('Delivery Comments') . ': ', 35, $subject->y, 'UTF-8');
            $subject->y -= 10;
            foreach ($commentLines as $comment) {
                $page->drawText(trim($comment), 45, $subject->y, 'UTF-8');
                $subject->y -= 10;
            }
            $subject->y -= 5;
        }
    }

    /**
     * Get order for current PDF page.
     * GetPdf method contains array of Shipment (or Invoice), in this method we search current Shipment (or Invoice)
     *
     * @param string $text
     * @param Phrase|string $phrasePrefix
     * @return OrderInterface|null
     */
    private function getCurrentOrder(string $text, $phrasePrefix = ''): ?OrderInterface
    {
        if (!count($this->objects)) {
            return null;
        }
        // if we cant find which shipment (or Invoice) element on current page, then just take first.
        $currentObject = current($this->objects);
        foreach ($this->objects as $object) {
            if ($phrasePrefix . $object->getIncrementId() === $text) {
                $currentObject = $object;
                break;
            }
        }

        return $currentObject->getOrder();
    }

    /**
     * Get Delivery Date entity for current Order
     *
     * @param OrderInterface $order
     * @return DeliveryDateOrderInterface|null
     */
    private function getCurrentDeliveryDate(OrderInterface $order): ?DeliveryDateOrderInterface
    {
        try {
            $deliveryDate = $this->dateRepository->getByOrderId((int)$order->getEntityId());
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $deliveryDate;
    }
}
