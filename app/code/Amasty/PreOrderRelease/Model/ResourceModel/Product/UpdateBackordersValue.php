<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\ResourceModel\Product;

use Magento\Framework\App\ResourceConnection;

class UpdateBackordersValue
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(array $productIds, int $backordersValue): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $this->resourceConnection->getTableName('cataloginventory_stock_item'),
            ['backorders' => $backordersValue, 'use_config_backorders' => '0'],
            ['product_id IN (?)' => $productIds]
        );
    }
}
