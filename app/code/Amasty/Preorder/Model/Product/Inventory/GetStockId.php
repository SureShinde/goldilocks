<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\Inventory;

use Amasty\Preorder\Model\ResourceModel\Product\Inventory\LoadStockId;

class GetStockId
{
    /**
     * @var array
     */
    private $stockIds;

    /**
     * @var LoadStockId
     */
    private $loadStockId;

    public function __construct(LoadStockId $loadStockId)
    {
        $this->loadStockId = $loadStockId;
    }

    public function execute(string $websiteCode): int
    {
        if (!isset($this->stockIds[$websiteCode])) {
            $this->stockIds[$websiteCode] = $this->loadStockId->execute($websiteCode);
        }

        return $this->stockIds[$websiteCode];
    }
}
