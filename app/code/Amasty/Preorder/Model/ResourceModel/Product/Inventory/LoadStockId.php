<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\ResourceModel\Product\Inventory;

use Magento\Framework\App\ResourceConnection;

class LoadStockId
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(string $websiteCode): int
    {
        $select = $this->resourceConnection->getConnection()->select()
            ->from($this->resourceConnection->getTableName('inventory_stock_sales_channel'), ['stock_id'])
            ->where('type = \'website\' AND code = ?', $websiteCode);

        return (int) $this->resourceConnection->getConnection()->fetchOne($select);
    }
}
