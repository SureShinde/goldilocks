<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\InventoryIndexer\Indexer\SourceItem\Strategy\Sync;

use Amasty\Preorder\Model\Indexer\Product\Msi\PreorderProcessor as MsiPreorderProcessor;
use Magento\InventoryIndexer\Indexer\SourceItem\Strategy\Sync;

class ReindexPreorder
{
    /**
     * @var MsiPreorderProcessor
     */
    private $msiPreorderProcessor;

    public function __construct(MsiPreorderProcessor $msiPreorderProcessor)
    {
        $this->msiPreorderProcessor = $msiPreorderProcessor;
    }

    public function beforeExecuteList(
        Sync $subject,
        array $sourceItemIds
    ): void {
        $this->msiPreorderProcessor->reindexList($sourceItemIds);
    }
}
