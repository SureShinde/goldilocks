<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\ResourceModel\Product\PreorderIndex;

use Amasty\Preorder\Model\ResourceModel\Product\PreorderIndex;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class JoinToProductCollection
 *
 * Join preorder index table to product collection.
 */
class JoinToProductCollection
{
    public const ATTRIBUTE_NAME = 'amasty_preorder';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function execute(ProductCollection $productCollection): void
    {
        if (!$productCollection->getFlag(self::ATTRIBUTE_NAME)) {
            $filters = $productCollection->getLimitationFilters();
            if (isset($filters['website_ids'])) {
                $condition = sprintf(
                    'product_website.product_id=preorder_index.%s AND product_website.website_id=preorder_index.%s',
                    PreorderIndex::PRODUCT_ID,
                    PreorderIndex::WEBSITE_ID
                );
            } else {
                $condition = sprintf(
                    'e.entity_id=preorder_index.%s AND preorder_index.%s = %d',
                    PreorderIndex::PRODUCT_ID,
                    PreorderIndex::WEBSITE_ID,
                    $this->getWebsiteId($productCollection)
                );
            }
            $productCollection->getSelect()->joinLeft(
                ['preorder_index' => $productCollection->getResource()->getTable(PreorderIndex::MAIN_TABLE)],
                $condition,
                [self::ATTRIBUTE_NAME => PreorderIndex::PRODUCT_ID]
            );
            $productCollection->setFlag(self::ATTRIBUTE_NAME, true);
        }
    }

    private function getWebsiteId(ProductCollection $productCollection): int
    {
        $storeId = $productCollection->getStoreId();
        if ($storeId) {
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        } else {
            $websiteId = $this->storeManager->getDefaultStoreView()->getWebsiteId();
        }

        return (int) $websiteId;
    }
}
