<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\ResourceModel\Product\Inventory;

use Amasty\Preorder\Model\Product\Inventory\GetSourceCodes;
use Amasty\Preorder\Model\Product\Inventory\GetStockId;
use Magento\Framework\App\ResourceConnection;

/**
 * Class LoadQtyByProductSkus
 *
 * Resolve products qty for MSI.
 */
class LoadQtyByProductSkus
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetSourceCodes
     */
    private $getSourceCodes;

    /**
     * @var GetStockId
     */
    private $getStockId;

    public function __construct(
        ResourceConnection $resourceConnection,
        GetSourceCodes $getSourceCodes,
        GetStockId $getStockId
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getSourceCodes = $getSourceCodes;
        $this->getStockId = $getStockId;
    }

    public function execute(array $productSkus, string $websiteCode): array
    {
        $itemsQty = $this->getItemsQty($productSkus, $websiteCode);
        $reservationsQty = $this->getReservationsQty($productSkus, $websiteCode);

        foreach ($itemsQty as $sku => $qty) {
            if (isset($reservationsQty[$sku])) {
                $itemsQty[$sku] += $reservationsQty[$sku];
            }
        }

        return $itemsQty;
    }

    private function getItemsQty(array $productSkus, string $websiteCode): array
    {
        $select = $this->resourceConnection->getConnection()->select()
            ->from($this->resourceConnection->getTableName('inventory_source_item'), [
                'sku',
                'qty' => 'SUM(quantity)'
            ])
            ->where('source_code IN (?)', $this->getSourceCodes->execute($websiteCode))
            ->where('sku IN (?)', $productSkus)
            ->group('sku')
            ->order('sku');

        return $this->resourceConnection->getConnection()->fetchPairs($select);
    }

    private function getReservationsQty(array $productSkus, string $websiteCode): array
    {
        $select = $this->resourceConnection->getConnection()->select()
            ->from($this->resourceConnection->getTableName('inventory_reservation'), [
                'sku',
                'qty' => 'SUM(quantity)'
            ])
            ->where('sku IN (?)', $productSkus)
            ->where('stock_id = ?', $this->getStockId->execute($websiteCode))
            ->order('sku');

        return $this->resourceConnection->getConnection()->fetchPairs($select);
    }
}
