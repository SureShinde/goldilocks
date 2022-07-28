<?php

namespace Magenest\GoogleTagManager\Model\Catalog\Category;

class CurrentCategoryResolver
{
    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    private $resolver;

    public function __construct(
        \Magento\Catalog\Model\Layer\Resolver $resolver
    ) {
        $this->resolver = $resolver;
    }

    public function getCurrentCategory()
    {
        if ($this->resolver->get()) {
            return $this->resolver->get()->getCurrentCategory();
        }
    }
}
