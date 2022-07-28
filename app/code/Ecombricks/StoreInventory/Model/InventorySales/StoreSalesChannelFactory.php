<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Model\InventorySales;

/**
 * Store sales channel factory
 */
class StoreSalesChannelFactory extends \Ecombricks\Common\Object\Factory
{

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $instanceName
     * @return void
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $instanceName = \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::class
    )
    {
        $this->storeManager = $storeManager;
        parent::__construct($objectManager, $instanceName);
    }

    /**
     * Create by store
     *
     * @param null|string|bool|int|\Magento\Store\Api\Data\StoreInterface $store
     * @return \Magento\InventorySalesApi\Api\Data\SalesChannelInterface
     */
    public function createByStore($store = null)
    {
        return parent::create([
            'data' => [
                'type' => \Ecombricks\StoreInventory\Api\InventorySalesApi\Data\SalesChannelInterface::TYPE_STORE,
                'code' => $this->storeManager->getStore($store)->getCode(),
            ]
        ]);
    }

}
