<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\SeoRichData\Block\Product;

use Amasty\Preorder\Model\Product\GetPreorderInformation;
use Amasty\SeoRichData\Block\Product as RichDataBlock;
use Magento\Catalog\Model\Product;

class ChangeAvailabilityCondition
{
    public const PRE_ORDER = 'http://schema.org/PreOrder';

    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    public function __construct(GetPreorderInformation $getPreorderInformation)
    {
        $this->getPreorderInformation = $getPreorderInformation;
    }

    public function afterGetAvailabilityCondition(RichDataBlock $subject, string $result, Product $product): string
    {
        if ($this->getPreorderInformation->execute($product)->isPreorder()) {
            $result = self::PRE_ORDER;
        }

        return $result;
    }
}
