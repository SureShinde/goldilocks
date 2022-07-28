<?php

namespace Magenest\GoogleTagManager\Block;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Json\Helper\Data as JsonDataHelper;
use Magento\Framework\View\Element\Template\Context;
use Magenest\GoogleTagManager\Api\ProductObjectGeneratorInterface;
use Magenest\GoogleTagManager\Helper\AttributeCodes;
use Magenest\GoogleTagManager\Helper\Data as GtmDataHelper;

class CategoryGtm extends ProductList
{
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $catalogLayer;

    /**
     * @param Context $context
     * @param JsonDataHelper $jsonHelper
     * @param Resolver $catalogLayerResolver
     * @param AttributeCodes $attributeCodes
     * @param GtmDataHelper $gtmHelper
     * @param ProductObjectGeneratorInterface $productObjectGenerator
     * @param array $data
     */
    public function __construct(
        Context $context,
        JsonDataHelper $jsonHelper,
        Resolver $catalogLayerResolver,
        AttributeCodes $attributeCodes,
        GtmDataHelper $gtmHelper,
        ProductObjectGeneratorInterface $productObjectGenerator,
        array $data = []
    ) {
        parent::__construct($context, $attributeCodes, $jsonHelper, $gtmHelper, $productObjectGenerator, $data);

        $this->catalogLayer = $catalogLayerResolver->get();
    }

    /**
     * Retrieves a current category
     *
     * @return Category
     */
    public function getCurrentCategory()
    {
        return $this->catalogLayer->getCurrentCategory();
    }

    /**
     * Retrieves name of the current category
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCategoryName()
    {
        $category = $this->getCurrentCategory();

        if ($this->_storeManager->getStore()->getRootCategoryId() != $category->getId()) {
            return $category->getName();
        }

        return '';
    }

    public function getBasicItemAttributes($object)
    {
        $attributes = parent::getBasicItemAttributes($object);
        $attributes['category'] = $this->getCurrentCategoryName();

        return $attributes;
    }

    public function getListName()
    {
        return 'Category Page';
    }
}
