<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Block\Sales\Adminhtml\Order\Invoice\Create;

/**
 * Create invoice form plugin
 */
class Form
{

    /**
     * Get sources assigned to stock ordered by priority
     *
     * @var \Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface
     */
    protected $getSourcesAssignedToStockOrderedByPriority;

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Constructor
     *
     * @param \Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     */
    public function __construct(
        \Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
    )
    {
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
        $this->getStockIdByStore = $getStockIdByStore;
    }

    /**
     * After can create shipment
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\Invoice\Create\Form $subject
     * @param bool $result
     * @return bool
     */
    public function afterCanCreateShipment(\Magento\Sales\Block\Adminhtml\Order\Invoice\Create\Form $subject, bool $result)
    {
        try {
            $stockId = $this->getStockIdByStore->execute($subject->getOrder()->getStore());
            $sources = $this->getSourcesAssignedToStockOrderedByPriority->execute($stockId);
            if (count($sources) > 1) {
                return false;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            return false;
        }
        return $result;
    }

}
