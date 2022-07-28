<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Catalog\Helper\Product\View;

use Amasty\Preorder\Model\Product\GetPreorderInformation;
use Magento\Catalog\Helper\Product\View as MagentoView;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Result\Page as ResultPage;

class AddHandle
{
    public const PREORDER_HANDLE = 'amasty_preorder_product';

    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    public function __construct(GetPreorderInformation $getPreorderInformation)
    {
        $this->getPreorderInformation = $getPreorderInformation;
    }

    /**
     * @param MagentoView $subject
     * @param ResultPage $resultPage
     * @param Product $product
     * @param null|mixed $params
     * @return array
     */
    public function beforeInitProductLayout(
        MagentoView $subject,
        ResultPage $resultPage,
        $product,
        $params = null
    ): array {
        if ($this->getPreorderInformation->execute($product)->isPreorder()) {
            $resultPage->addHandle(self::PREORDER_HANDLE);
        }

        return [$resultPage, $product, $params];
    }
}
