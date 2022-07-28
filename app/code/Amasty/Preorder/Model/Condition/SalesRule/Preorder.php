<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Condition\SalesRule;

use Amasty\Preorder\Model\Quote\Item\GetPreorderInformation;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Rule\Model\Condition\AbstractCondition;

class Preorder extends AbstractCondition
{
    public const SELECT_TYPE = 'select';

    /**
     * @param AbstractModel|QuoteItem $model
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        $result = false;
        /** @var GetPreorderInformation $getPreorderInformation */
        if ($getPreorderInformation = $this->getData('getPreorderInformation')) {
            $result = $getPreorderInformation->execute($model)->isPreorder() == $this->getValue();
        }

        return $result;
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
}
