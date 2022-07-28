<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Indexer\Product\Msi;

use Amasty\Preorder\Model\Indexer\Product\PreorderIndexer as MainPreorderIndexer;
use Exception;
use Magento\Framework\Mview\ActionInterface as MviewInterface;

class PreorderIndexer implements MviewInterface
{
    /**
     * @var MainPreorderIndexer
     */
    private $preorderIndexer;

    /**
     * @var ConvertSourceItemIds
     */
    private $convertSourceItemIds;

    public function __construct(MainPreorderIndexer $preorderIndexer, ConvertSourceItemIds $convertSourceItemIds)
    {
        $this->preorderIndexer = $preorderIndexer;
        $this->convertSourceItemIds = $convertSourceItemIds;
    }

    /**
     * @param int[] $ids
     * @return void
     * @throws Exception
     */
    public function execute($ids)
    {
        $this->preorderIndexer->execute(
            $this->convertSourceItemIds->execute($ids)
        );
    }
}
