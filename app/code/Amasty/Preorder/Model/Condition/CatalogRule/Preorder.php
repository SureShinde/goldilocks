<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Condition\CatalogRule;

use Amasty\Preorder\Model\Product\Detect\IsProductPreorderInterface;
use Amasty\Preorder\Model\ResourceModel\Product\PreorderIndex\JoinToProductCollection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;

class Preorder extends AbstractCondition
{
    public const SELECT_TYPE = 'select';

    /**
     * @param AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        return (bool) $model->getData(JoinToProductCollection::ATTRIBUTE_NAME) === (bool) $this->getValue();
    }

    /**
     * Validate product by entity ID
     *
     * @param int $productId
     * @return bool
     */
    public function validateByEntityId($productId): bool
    {
        /** @var IsProductPreorderInterface $isProductPreorder */
        if ($isProductPreorder = $this->getData('isProductPreorder')) {
            /** @var ProductRepositoryInterface $productRepository */
            $productRepository = $this->getData('productRepository');
            $result = $isProductPreorder->execute($productRepository->getById($productId));
        }

        return $result ?? false;
    }

    /** Join preorder index info.
     *
     * @param ProductCollection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        /** @var JoinToProductCollection $joinToProductCollection */
        if ($joinToProductCollection = $this->getData('joinToProductCollection')) {
            $joinToProductCollection->execute($productCollection);
        }

        return $this;
    }

    /**
     * Get input type for attribute value.
     *
     * @return string
     */
    public function getValueElementType()
    {
        return self::SELECT_TYPE;
    }

    /**
     * Init value select options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        $this->setValueOption([__('No'), __('Yes')]);
        return $this;
    }

    /**
     * Get HTML of condition string
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . __('Pre-order (Amasty Pre Order) %1', $this->getValueElementHtml())
            . $this->getRemoveLinkHtml();
    }

    /**
     * @return string
     */
    public function getMappedSqlField()
    {
        return 'preorder_index.product_id';
    }

    /**
     * @return string
     */
    public function getOperatorForValidate()
    {
        return $this->getValue() ? '>=' : '==';
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return self::SELECT_TYPE;
    }

    public function collectConditionSql(): string
    {
        if ($this->getValue()) {
            $condition = '%s IS NOT NULL';
        } else {
            $condition = '%s IS NULL';
        }
        return sprintf($condition, $this->getMappedSqlField());
    }
}
