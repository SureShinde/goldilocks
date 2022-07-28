<?php

namespace Magenest\GoogleTagManager\Model\Catalog\Category;

use Magento\Store\Model\StoreManagerInterface;

class NameResolver
{
    /**
     * @var AttributeValueExtractor
     */
    private $extractor;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var int
     */
    private $maxCategories;
    /**
     * @var string
     */
    private $slashesReplacement;
    /**
     * @var string
     */
    private $nameAttribute;

    public function __construct(
        AttributeValueExtractor $extractor,
        StoreManagerInterface $storeManager,
        $maxCategories = 5,
        $nameAttribute = 'name',
        $slashesReplacement = '-'
    ) {
        $this->extractor = $extractor;
        $this->storeManager = $storeManager;
        $this->maxCategories = $maxCategories;
        $this->nameAttribute = $nameAttribute;
        $this->slashesReplacement = $slashesReplacement;
    }

    /**
     * Figures out the value for 'category' field on product objects.
     * - Limits total number of category names to 5
     * - Ignores root category and its parents
     *
     * @param \Magento\Catalog\Model\Category $category
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve(\Magento\Catalog\Model\Category $category)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $pathIds = $category->getPathIds();
        $pathIds = $this->trimRootCategoryPath($pathIds);

        // root category name should never be reported
        if (!$pathIds) {
            return '';
        }

        // current category is re-added later
        \array_pop($pathIds);

        $pathIds = \array_slice($pathIds, 0, $this->maxCategories - 1);

        if (!$pathIds) {
            $names = [];
        } else {
            $names = $this->extractor->get($pathIds, $this->nameAttribute, $storeId);
        }

        $names[] = $category->getName();

        // replace slashes in category names
        $names = \array_map(function ($name) {
            return \str_replace('/', $this->slashesReplacement, $name);
        }, $names);

        return \implode('/', $names);
    }

    private function trimRootCategoryPath($pathIds)
    {
        $rootCategory = $this->storeManager->getGroup()->getRootCategoryId();
        $rootPos = \array_search($rootCategory, $pathIds, false);
        if ($rootPos !== false) {
            $pathIds = \array_slice($pathIds, $rootPos + 1);
        }

        return $pathIds;
    }
}
