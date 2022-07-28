<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\ResourceModel\Product\Inventory;

use Exception;
use Magento\Framework\App\ResourceConnection;

class LoadProductIdsByItemIds
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param array $sourceItemIds
     * @return array
     * @throws Exception
     */
    public function execute(array $sourceItemIds): array
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            ['cpe' => $this->resourceConnection->getTableName('catalog_product_entity')],
            ['cpe.entity_id']
        )->join(
            ['isi' => $this->resourceConnection->getTableName('inventory_source_item')],
            $this->resourceConnection->getConnection()->prepareSqlCondition(
                'isi.source_item_id',
                ['in' => $sourceItemIds]
            ),
            []
        );

        return (array) $this->resourceConnection->getConnection()->fetchRow($select);
    }
}
