<?php

declare(strict_types=1);

namespace Amasty\PreOrderGraphQl\Model\Resolver;

use Amasty\Preorder\Model\Product\GetPreorderInformation;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class PreOrderInformation implements ResolverInterface
{
    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    public function __construct(GetPreorderInformation $getPreorderInformation)
    {
        $this->getPreorderInformation = $getPreorderInformation;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $infoModel = $this->getPreorderInformation->execute($value['model']);
        
        return [
            'preorder_flag' => $infoModel->isPreorder(),
            'note' => $infoModel->getNote(),
            'cart_label' => $infoModel->getCartLabel()
        ];
    }
}
