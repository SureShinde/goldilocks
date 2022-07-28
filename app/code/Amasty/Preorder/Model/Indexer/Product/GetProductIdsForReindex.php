<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Indexer\Product;

use Amasty\Preorder\Model\ResourceModel\Product\LoadRelationsByChild;

class GetProductIdsForReindex
{
    /**
     * @var LoadRelationsByChild
     */
    private $loadRelationsByChild;

    public function __construct(LoadRelationsByChild $loadRelationsByChild)
    {
        $this->loadRelationsByChild = $loadRelationsByChild;
    }

    public function execute(array $productIds): array
    {
        $parentIds = $this->loadRelationsByChild->execute($productIds);
        $productIds = array_unique(array_merge($parentIds, $productIds));

        return $productIds;
    }
}
