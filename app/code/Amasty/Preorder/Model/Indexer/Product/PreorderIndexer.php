<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Indexer\Product;

use Amasty\Preorder\Model\Indexer\Product\Action\ExecuteFull;
use Amasty\Preorder\Model\Indexer\Product\Action\ExecutePartial;
use Exception;
use Magento\Framework\Indexer\ActionInterface as IndexerInterface;
use Magento\Framework\Mview\ActionInterface as MviewInterface;

class PreorderIndexer implements IndexerInterface, MviewInterface
{
    /**
     * @var ExecuteFull
     */
    private $executeFull;

    /**
     * @var ExecutePartial
     */
    private $executePartial;

    public function __construct(ExecuteFull $executeFull, ExecutePartial $executePartial)
    {
        $this->executeFull = $executeFull;
        $this->executePartial = $executePartial;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function executeFull()
    {
        $this->executeFull->execute();
    }

    /**
     * @param int[] $ids
     * @return void
     * @throws Exception
     */
    public function executeList(array $ids)
    {
        $this->executePartial->execute($ids);
    }

    /**
     * @param int $id
     * @return void
     * @throws Exception
     */
    public function executeRow($id)
    {
        $this->executePartial->execute([$id]);
    }

    /**
     * @param int[] $ids
     * @return void
     * @throws Exception
     */
    public function execute($ids)
    {
        $this->executePartial->execute($ids);
    }
}
