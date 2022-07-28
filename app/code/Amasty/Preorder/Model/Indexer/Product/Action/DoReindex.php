<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Indexer\Product\Action;

use Amasty\Preorder\Model\Indexer\Product\CacheContext;
use Amasty\Preorder\Model\Product\Detect\IsProductPreorderInterface;
use Amasty\Preorder\Model\Product\Detect\IsProductPreorderInterfaceFactory;
use Amasty\Preorder\Model\ResourceModel\Inventory;
use Amasty\Preorder\Model\ResourceModel\Product\PreorderIndex;
use Amasty\Preorder\Model\ResourceModel\Product\PreorderIndex\TableWorker;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

class DoReindex
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Iterator
     */
    private $resourceIterator;

    /**
     * @var array
     */
    private $productIds = [];

    /**
     * @var IsProductPreorderInterfaceFactory
     */
    private $isProductPreorderFactory;

    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var TableWorker
     */
    private $tableWorker;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var int
     */
    private $batchCount;

    /**
     * @var int
     */
    private $batchCacheCount;

    public function __construct(
        StoreManagerInterface $storeManager,
        ProductCollectionFactory $productCollectionFactory,
        Iterator $resourceIterator,
        IsProductPreorderInterfaceFactory $isProductPreorderFactory,
        Inventory $inventory,
        TableWorker $tableWorker,
        CacheContext $cacheContext,
        ManagerInterface $eventManager,
        int $batchCount = 1000,
        int $batchCacheCount = 100
    ) {
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resourceIterator = $resourceIterator;
        $this->isProductPreorderFactory = $isProductPreorderFactory;
        $this->inventory = $inventory;
        $this->tableWorker = $tableWorker;
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
        $this->batchCount = $batchCount;
        $this->batchCacheCount = $batchCacheCount;
    }

    public function execute(?array $productIds = null): void
    {
        $validatedProductIds = $this->validateProducts($productIds);

        $count = 0;
        $rows = [];
        foreach ($validatedProductIds as $productId => $websiteIds) {
            while ($websiteIds) {
                $rows[] = [
                    PreorderIndex::PRODUCT_ID => $productId,
                    PreorderIndex::WEBSITE_ID => array_shift($websiteIds)
                ];
                if (++$count > $this->batchCount) {
                    $this->tableWorker->insert($rows);
                    $count = 0;
                    $rows = [];
                }
            }
            $this->registerEntities(Product::CACHE_TAG, [$productId]);
        }
        if ($rows) {
            $this->tableWorker->insert($rows);
        }
        $this->cleanCache();
    }

    private function validateProducts(?array $productIds): array
    {
        $this->inventory->clearQtyCache();

        $productCollection = $this->productCollectionFactory->create();
        if ($productIds !== null) {
            $productCollection->addIdFilter($productIds);
        }

        $this->resourceIterator->walk(
            $productCollection->getSelect(),
            [[$this, 'callbackValidateProduct']],
            [
                'product' => $productCollection->getNewEmptyItem(),
                'isProductPreorderModel' => $this->isProductPreorderFactory->create()
            ]
        );

        $validatedProducts = $this->productIds;
        $this->productIds = [];

        return $validatedProducts;
    }

    public function callbackValidateProduct(array $args): void
    {
        /** @var IsProductPreorderInterface $isProductPreorder */
        $isProductPreorder = $args['isProductPreorderModel'];

        $product = $args['product'];
        $product->setData($args['row']);
        $product->setTypeInstance(null); // clear type instance for correct detect isComposite

        /** @var Website $website */
        foreach ($this->storeManager->getWebsites() as $website) {
            if (!$website->getDefaultStore()) {
                continue;
            }

            $product->setStoreId($website->getDefaultStore()->getId());
            if ($isProductPreorder->execute($product)) {
                $this->productIds[$product->getId()][] = $website->getId();
            }
        }
    }

    private function registerEntities(string $cacheTag, array $ids): void
    {
        $this->cacheContext->registerEntities($cacheTag, $ids);
        if ($this->cacheContext->getSize() > $this->batchCacheCount) {
            $this->cleanCache();
        }
    }

    private function cleanCache(): void
    {
        if ($this->cacheContext->getSize() > 0) {
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
            $this->cacheContext->flush();
        }
    }
}
