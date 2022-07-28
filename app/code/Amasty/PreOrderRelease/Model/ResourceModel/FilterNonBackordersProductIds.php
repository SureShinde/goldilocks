<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\ResourceModel;

use Amasty\Preorder\Model\Product\Constants;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ResourceConnection;

class FilterNonBackordersProductIds
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StockConfigurationInterface
     */
    private $configuration;

    public function __construct(ResourceConnection $resourceConnection, StockConfigurationInterface $configuration)
    {
        $this->resourceConnection = $resourceConnection;
        $this->configuration = $configuration;
    }

    /**
     * @param string[] $productIds
     * @return string[]
     */
    public function execute(array $productIds): array
    {
        $applicableForGlobal = $this->configuration->getBackorders() !== Constants::BACKORDERS_PREORDER_OPTION;

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            ['stock_item' => $this->resourceConnection->getTableName('cataloginventory_stock_item')],
            'product_id'
        )->where(
            'product_id IN (?)',
            $productIds
        )->where(
            'backorders != ?',
            Constants::BACKORDERS_PREORDER_OPTION
        )->where(
            sprintf('use_config_backorders != 1 OR 1=%d', (int) $applicableForGlobal)
        );

        return (array) $connection->fetchCol($select);
    }
}
