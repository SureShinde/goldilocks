<?php

namespace Magenest\GoogleTagManager\Model\Catalog\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class AttributeValueExtractor
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var string[][]
     */
    private $cache = [];

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Extract attribute code for supplied categories
     *
     * @param array $categoryIds
     * @param string $attributeCode
     * @param int $storeId
     *
     * @return string[]
     */
    public function get(array $categoryIds, string $attributeCode, $storeId)
    {
        $uncachedIds = [];
        foreach ($categoryIds as $id) {
            $cached = $this->cacheGet($id, $attributeCode, $storeId);
            if (!$cached) {
                $uncachedIds[] = $id;
            }
        }

        if ($uncachedIds) {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addIdFilter($uncachedIds);
            $collection->setStoreId($storeId);
            $collection->addAttributeToSelect($attributeCode);

            /** @var Category $category */
            foreach ($collection as $category) {
                $value = $category->getData($attributeCode);
                $this->cacheSet($category->getId(), $attributeCode, $storeId, $value);
            }
        }

        $values = [];
        foreach ($categoryIds as $id) {
            $values[] = $this->cacheGet($id, $attributeCode, $storeId);
        }

        return $values;
    }

    /**
     * @param int $id
     * @param string $attributeCode
     * @param int $storeId
     * @return string|null
     * @SuppressWarnings(PHPMD.ShortVariable) (for $id)
     */
    private function cacheGet($id, $attributeCode, $storeId)
    {
        return $this->cache[$attributeCode][$storeId][$id] ?? null;
    }

    /**
     * @param int $id
     * @param string $attributeCode
     * @param int $storeId
     * @param string $value
     * @SuppressWarnings(PHPMD.ShortVariable) (for $id)
     */
    private function cacheSet($id, $attributeCode, $storeId, $value)
    {
        $this->cache[$attributeCode][$storeId][$id] = $value;
    }
}
