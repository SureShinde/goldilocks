<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventoryReservationCli\SalableQuantityInconsistency;

if (version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.2', '>=')) :

/**
 * Salable quantity inconsistency collector plugin
 */
class Collector extends \Ecombricks\Common\Plugin\Plugin
{

    /**
     * Salable quantity inconsistency factory
     *
     * @var \Magento\InventoryReservationCli\Model\SalableQuantityInconsistencyFactory
     */
    protected $salableQuantityInconsistencyFactory;

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Object\WrapperFactory $wrapperFactory
     * @param \Magento\InventoryReservationCli\Model\SalableQuantityInconsistencyFactory $salableQuantityInconsistencyFactory
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Magento\InventoryReservationCli\Model\SalableQuantityInconsistencyFactory $salableQuantityInconsistencyFactory,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
    )
    {
        parent::__construct($wrapperFactory);
        $this->salableQuantityInconsistencyFactory = $salableQuantityInconsistencyFactory;
        $this->getStockIdByStore = $getStockIdByStore;
    }

    /**
     * Around add order
     *
     * @param \Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\Collector $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return void
     */
    public function aroundAddOrder(
        \Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\Collector $subject,
        \Closure $proceed,
        \Magento\Sales\Api\Data\OrderInterface $order
    ): void
    {
        $this->setSubject($subject);
        $items = $this->getSubjectPropertyValue('items');
        $key = $order->getEntityId().'-'.$this->getStockIdByStore->execute($order->getStore());
        if (!isset($items[$key])) {
            $items[$key] = $this->salableQuantityInconsistencyFactory->create();
        }
        if (version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.4', '>=')) {
            $items[$key]->setOrderIncrementId($order->getIncrementId());
            $items[$key]->setOrderStatus($order->getStatus());
        } else {
            $items[$key]->setOrder($order);
        }
        $this->setSubjectPropertyValue('items', $items);
    }

    /**
     * Around add order data
     *
     * @param \Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\Collector $subject
     * @param \Closure $proceed
     * @param array $orderData
     */
    public function aroundAddOrderData(
        \Magento\InventoryReservationCli\Model\SalableQuantityInconsistency\Collector $subject,
        \Closure $proceed,
        array $orderData
    ): void
    {
        $this->setSubject($subject);
        if (version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.4', '>=')) {
            $items = $this->getSubjectPropertyValue('items');
            $objectId = $orderData['entity_id'];
            $stockId = $this->getStockIdByStore->execute((int) $orderData['store_id']);
            $key = $objectId.'-'.$stockId;
            if (!isset($items[$key])) {
                $items[$key] = $this->salableQuantityInconsistencyFactory->create();
            }
            $items[$key]->setOrderIncrementId($orderData['increment_id']);
            $items[$key]->setOrderStatus($orderData['status']);
            $this->setSubjectPropertyValue('items', $items);
        }
    }

}

endif;
