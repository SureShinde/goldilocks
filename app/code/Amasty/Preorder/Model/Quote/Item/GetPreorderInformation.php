<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Quote\Item;

use Amasty\Preorder\Api\Data\CartItemInformationInterface;
use Magento\Quote\Api\Data\CartItemInterface;

class GetPreorderInformation
{
    /**
     * @var Processor
     */
    private $processor;

    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    public function execute(CartItemInterface $cartItem): CartItemInformationInterface
    {
        if ($cartItem->getExtensionAttributes()->getPreorderInfo() === null) {
            $this->processor->execute([$cartItem]);
        }

        return $cartItem->getExtensionAttributes()->getPreorderInfo();
    }
}
