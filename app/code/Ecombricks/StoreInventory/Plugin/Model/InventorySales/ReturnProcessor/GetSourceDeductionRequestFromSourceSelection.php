<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventorySales\ReturnProcessor;

/**
 * Get source deduction request from source selection plugin
 */
class GetSourceDeductionRequestFromSourceSelection extends \Ecombricks\Common\Plugin\Plugin
{

    /**
     * Store sales channel factory
     *
     * @var \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory
     */
    protected $storeSalesChannelFactory;

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Object\WrapperFactory $wrapperFactory
     * @param \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
    )
    {
        parent::__construct($wrapperFactory);
        $this->storeSalesChannelFactory = $storeSalesChannelFactory;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventorySales\Model\ReturnProcessor\GetSourceDeductionRequestFromSourceSelection $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface $sourceSelectionResult
     * @return array|\Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundExecute(
        \Magento\InventorySales\Model\ReturnProcessor\GetSourceDeductionRequestFromSourceSelection $subject,
        \Closure $proceed,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface $sourceSelectionResult
    ): array
    {
        $this->setSubject($subject);
        $salesEventFactory = $this->getSubjectPropertyValue('salesEventFactory');
        $sourceDeductionRequestFactory = $this->getSubjectPropertyValue('sourceDeductionRequestFactory');
        $sourceDeductionRequests = [];
        $salesChannel = $this->storeSalesChannelFactory->createByStore($order->getStore());
        $salesEvent = $salesEventFactory->create([
            'type' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::EVENT_CREDITMEMO_CREATED,
            'objectType' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId' => (string) $order->getEntityId(),
        ]);
        $sourceSelectionItems = $sourceSelectionResult->getSourceSelectionItems();
        foreach ($this->invokeSubjectMethod('getItemsPerSource', $sourceSelectionItems) as $sourceCode => $items) {
            $sourceDeductionRequests[] = $sourceDeductionRequestFactory->create([
                'sourceCode' => $sourceCode,
                'items' => $items,
                'salesChannel' => $salesChannel,
                'salesEvent' => $salesEvent,
            ]);
        }
        return $sourceDeductionRequests;
    }

}
