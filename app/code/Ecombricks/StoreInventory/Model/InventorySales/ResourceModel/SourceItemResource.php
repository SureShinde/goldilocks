<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_AdvancedMSI
 */


declare(strict_types=1);

namespace Ecombricks\StoreInventory\Model\InventorySales\ResourceModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;

class SourceItemResource
{
    const NOTIFY_QTY_TABLE = 'inventory_low_stock_notification_configuration';

    const INVENTORY_ITEM_TABLE = 'inventory_source_item';

    const INVENTORY_SOURCE_TABLE = 'inventory_source';

    const SALES_ORDER = 'sales_order';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ResourceConnection $resourceConnection,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $resourceConnection->getConnection();
        $this->scopeConfig = $scopeConfig;
    }

    public function getSourceItemData($productSku, $sourceCode)
    {
        $inventoryItemTable = $this->resourceConnection->getTableName(self::INVENTORY_ITEM_TABLE);

        $select = $this->connection->select()
            ->from($inventoryItemTable)
            ->where(SourceItemConfigurationInterface::SOURCE_CODE . ' = ?', $sourceCode)
            ->where(SourceItemConfigurationInterface::SKU . ' = ?', $productSku);

        return $this->connection->fetchRow($select);
    }

    public function getNotifyQtyForProduct($productSku, $sourceCode)
    {
        $notifyTable = $this->resourceConnection->getTableName(self::NOTIFY_QTY_TABLE);

        $select = $this->connection->select()
            ->from($notifyTable)
            ->where(SourceItemConfigurationInterface::SOURCE_CODE . ' = ?', $sourceCode)
            ->where(SourceItemConfigurationInterface::SKU . ' = ?', $productSku);

        $row = $this->connection->fetchRow($select);
        if (!isset($row['notify_stock_qty'])) {
            $defaultQty = $this->scopeConfig->getValue('cataloginventory/item_options/notify_stock_qty');
            $row['use_default'] = $defaultQty;
        }

        return $row;
    }

    public function getWarehousesCode($code)
    {
        $inventorySourceTable = $this->resourceConnection->getTableName(self::INVENTORY_SOURCE_TABLE);

        $select = $this->connection->select()
            ->from($inventorySourceTable)
            ->where(SourceItemConfigurationInterface::SOURCE_CODE . ' = ?', $code);

        return $this->connection->fetchRow($select);
    }

    public function getOrderDetails($incrementId)
    {
        $inventorySourceTable = $this->resourceConnection->getTableName(self::SALES_ORDER);

        $select = $this->connection->select()
            ->from($inventorySourceTable)
            ->where('increment_id' . ' = ?', $incrementId);

        return $this->connection->fetchRow($select);
    }

}
