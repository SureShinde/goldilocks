<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\ResourceModel\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class PreorderIndex
{
    public const MAIN_TABLE = 'amasty_preorder_product_index';
    public const REPLICA_TABLE = 'amasty_preorder_product_index_replica';

    public const PRODUCT_ID = 'product_id';
    public const WEBSITE_ID = 'website_id';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

    public function getTableName(string $tableName): string
    {
        return $this->resourceConnection->getTableName($tableName);
    }
}
