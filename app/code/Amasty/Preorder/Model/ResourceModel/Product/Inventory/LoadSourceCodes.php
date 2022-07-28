<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\ResourceModel\Product\Inventory;

use Amasty\Preorder\Model\Product\Inventory\GetStockId;
use Magento\Framework\App\ResourceConnection;

class LoadSourceCodes
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetStockId
     */
    private $getStockId;

    public function __construct(ResourceConnection $resourceConnection, GetStockId $getStockId)
    {
        $this->resourceConnection = $resourceConnection;
        $this->getStockId = $getStockId;
    }

    public function execute(string $websiteCode): array
    {
        $select = $this->resourceConnection->getConnection()->select()
            ->from($this->resourceConnection->getTableName('inventory_source_stock_link'), ['source_code'])
            ->where('stock_id = ?', $this->getStockId->execute($websiteCode));

        return $this->resourceConnection->getConnection()->fetchCol($select);
    }
}
