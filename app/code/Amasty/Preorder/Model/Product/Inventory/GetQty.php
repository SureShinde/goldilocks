<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\Inventory;

use Amasty\Preorder\Model\ResourceModel\Inventory;

class GetQty implements GetQtyInterface
{
    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var QtyStorage
     */
    private $qtyStorage;

    public function __construct(
        QtyStorage $qtyStorage,
        Inventory $inventory
    ) {
        $this->inventory = $inventory;
        $this->qtyStorage = $qtyStorage;
    }

    public function execute(string $productSku, string $websiteCode): float
    {
        if (!$this->qtyStorage->isExist($productSku)) {
            $qty = $this->inventory->getQty($productSku, $websiteCode);
            $this->qtyStorage->set($productSku, $qty);
        }

        return $this->qtyStorage->get($productSku);
    }
}
