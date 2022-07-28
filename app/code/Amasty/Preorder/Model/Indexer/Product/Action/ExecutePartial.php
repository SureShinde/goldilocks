<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Indexer\Product\Action;

use Amasty\Preorder\Model\Indexer\Product\GetProductIdsForReindex;
use Amasty\Preorder\Model\ResourceModel\Product\PreorderIndex;
use Amasty\Preorder\Model\ResourceModel\Product\PreorderIndex\TableWorker;
use Exception;

class ExecutePartial
{
    /**
     * @var DoReindex
     */
    private $doReindex;

    /**
     * @var TableWorker
     */
    private $tableWorker;

    /**
     * @var GetProductIdsForReindex
     */
    private $getProductIdsForReindex;

    public function __construct(
        DoReindex $doReindex,
        TableWorker $tableWorker,
        GetProductIdsForReindex $getProductIdsForReindex
    ) {
        $this->doReindex = $doReindex;
        $this->tableWorker = $tableWorker;
        $this->getProductIdsForReindex = $getProductIdsForReindex;
    }

    /**
     * @param array $productIds
     * @return void
     * @throws Exception
     */
    public function execute(array $productIds): void
    {
        $this->tableWorker->createTemporaryTable();

        $productIds = $this->getProductIdsForReindex->execute($productIds);
        $this->doReindex->execute($productIds);

        $this->tableWorker->syncDataPartial([
            sprintf('%s IN (?)', PreorderIndex::PRODUCT_ID) => $productIds
        ]);
    }
}
