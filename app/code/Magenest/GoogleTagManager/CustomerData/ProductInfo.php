<?php

namespace Magenest\GoogleTagManager\CustomerData;

class ProductInfo implements \Magento\Customer\CustomerData\SectionSourceInterface
{
    /**
     * @var \Magenest\GoogleTagManager\Helper\CatalogSession
     */
    private $catalogSession;

    public function __construct(
        \Magenest\GoogleTagManager\Helper\CatalogSession $catalogSession
    ) {
        $this->catalogSession = $catalogSession;
    }

    /**
     * Returns formatted product information
     *
     * @return array|null
     */
    public function getSectionData()
    {
        return [
            'data' => $this->catalogSession->getProductData(),
        ];
    }
}
