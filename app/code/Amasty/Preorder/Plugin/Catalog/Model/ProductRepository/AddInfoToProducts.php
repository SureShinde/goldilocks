<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Catalog\Model\ProductRepository;

use Amasty\Preorder\Model\Product\Processor;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Model\ProductRepository;

class AddInfoToProducts
{
    /**
     * @var Processor
     */
    private $processor;

    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    public function afterGetList(
        ProductRepository $subject,
        ProductSearchResultsInterface $productSearchResults
    ): ProductSearchResultsInterface {
        $this->processor->execute($productSearchResults->getItems());

        return $productSearchResults;
    }
}
