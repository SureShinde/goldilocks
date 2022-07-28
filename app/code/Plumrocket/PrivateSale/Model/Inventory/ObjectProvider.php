<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\Inventory;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Module\Manager;

class ObjectProvider
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * Class name
     *
     * @var string
     */
    private $isSourceItemManagementAllowedForProductType = 'Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface';

    /**
     * Class name
     *
     * @var string
     */
    private $getSalableQuantityDataBySku = 'Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku';

    /**
     * @var string
     */
    private $getAssignedStockIdsBySku = 'Magento\InventorySalesAdminUi\Model\ResourceModel\GetAssignedStockIdsBySku';

    /**
     * @var string
     */
    private $getStockItemConfiguration = 'Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface';

    /**
     * @var string
     */
    private $getProductSalableQty = 'Magento\InventorySalesApi\Api\GetProductSalableQtyInterface';

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Manager $moduleManager
    ) {
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return \Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface|null
     */
    public function getObjectIsSourceItemManagementAllowedForProductType()
    {
        if ($this->moduleManager->isEnabled('Magento_InventoryConfigurationApi')) {
            return $this->objectManager->get($this->isSourceItemManagementAllowedForProductType);
        }

        return null;
    }

    /**
     * @return \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku|null
     */
    public function getObjectGetSalableQuantityDataBySkuObject()
    {
        if ($this->moduleManager->isEnabled('Magento_InventorySalesAdminUi')) {
            return $this->objectManager->get($this->getSalableQuantityDataBySku);
        }

        return null;
    }

    /**
     * @return \Magento\InventorySalesAdminUi\Model\ResourceModel\GetAssignedStockIdsBySku|null
     */
    public function getAssignedStockIdsBySku()
    {
        if ($this->moduleManager->isEnabled('Magento_InventorySalesAdminUi')) {
            return $this->objectManager->get($this->getAssignedStockIdsBySku);
        }

        return null;
    }

    /**
     * @return \Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface|null
     */
    public function getStockItemConfiguration()
    {
        if ($this->moduleManager->isEnabled('Magento_InventoryConfigurationApi')) {
            return $this->objectManager->get($this->getStockItemConfiguration);
        }

        return null;
    }

    /**
     * @return \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface|null
     */
    public function getProductSalableQty()
    {
        if ($this->moduleManager->isEnabled('Magento_InventorySalesApi')) {
            return $this->objectManager->get($this->getProductSalableQty);
        }

        return null;
    }
}
