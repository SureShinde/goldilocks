<?php

declare(strict_types=1);

namespace Amasty\Preorder\ViewModel\Quote\Item;

use Amasty\Preorder\Api\Data\CartItemInformationInterface;
use Amasty\Preorder\Model\Quote\Item\GetPreorderInformation;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Api\Data\CartItemInterface;

class PreorderInformation implements ArgumentInterface
{
    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    public function __construct(GetPreorderInformation $getPreorderInformation)
    {
        $this->getPreorderInformation = $getPreorderInformation;
    }

    public function getPreorderInformation(CartItemInterface $cartItem): CartItemInformationInterface
    {
        return $this->getPreorderInformation->execute($cartItem);
    }
}
