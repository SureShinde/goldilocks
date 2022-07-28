<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Observer\InventoryShipping;

/**
 * Virtual source deduction processor plugin
 */
class VirtualSourceDeductionProcessor extends \Ecombricks\Common\Plugin\InheritorPlugin
{

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Object\WrapperFactory $wrapperFactory
     * @param \Magento\InventoryShipping\Observer\VirtualSourceDeductionProcessor $parent
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Magento\InventoryShipping\Observer\VirtualSourceDeductionProcessor $parent
    )
    {
        parent::__construct($wrapperFactory);
        $this->setParent($parent);
    }

    /**
     * Around execute
     *
     * @param \Magento\InventoryShipping\Observer\VirtualSourceDeductionProcessor $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundExecute(
        \Magento\InventoryShipping\Observer\VirtualSourceDeductionProcessor $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    )
    {
        $this->setSubject($subject);
        $sourceDeductionRequestsFromSourceSelectionFactory = $this->getParentPropertyValue('sourceDeductionRequestsFromSourceSelectionFactory');
        $getSourceSelectionResultFromInvoice = $this->getParentPropertyValue('getSourceSelectionResultFromInvoice');
        $salesEventFactory = $this->getParentPropertyValue('salesEventFactory');
        $sourceDeductionService = $this->getParentPropertyValue('sourceDeductionService');
        $invoice = $observer->getEvent()->getInvoice();
        if (!$this->invokeParentMethod('isValid', $invoice)) {
            return;
        }
        $sourceDeductionRequests = $sourceDeductionRequestsFromSourceSelectionFactory->create(
            $getSourceSelectionResultFromInvoice->execute($invoice),
            $salesEventFactory->create([
                'type' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::EVENT_INVOICE_CREATED,
                'objectType' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::OBJECT_TYPE_ORDER,
                'objectId' => $invoice->getOrderId(),
            ]),
            (int) $invoice->getOrder()->getStore()->getId()
        );
        foreach ($sourceDeductionRequests as $sourceDeductionRequest) {
            $sourceDeductionService->execute($sourceDeductionRequest);
            $this->invokeParentMethod('placeCompensatingReservation', $sourceDeductionRequest);
        }
    }

}
