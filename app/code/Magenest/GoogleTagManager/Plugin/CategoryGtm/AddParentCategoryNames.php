<?php

namespace Magenest\GoogleTagManager\Plugin\CategoryGtm;

use Magenest\GoogleTagManager\Block\CategoryGtm;
use Magenest\GoogleTagManager\Helper\Data;

class AddParentCategoryNames
{
    /**
     * @var Data
     */
    private $data;
    /**
     * @var \Magenest\GoogleTagManager\Model\Catalog\Category\NameResolver
     */
    private $nameResolver;

    public function __construct(
        Data $data,
        \Magenest\GoogleTagManager\Model\Catalog\Category\NameResolver $nameResolver
    ) {
        $this->data = $data;
        $this->nameResolver = $nameResolver;
    }

    public function afterGetCurrentCategoryName(CategoryGtm $subject, $name)
    {
        if (!$this->data->reportParentCategories()) {
            return $name;
        }

        return $this->nameResolver->resolve($subject->getCurrentCategory());
    }
}
