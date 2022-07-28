<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Data;

use Amasty\Preorder\Api\Data\ProductInformationInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class ProductInformation extends AbstractSimpleObject implements ProductInformationInterface
{
    public const PREORDER_FLAG = 'preorder_flag';
    public const NOTE = 'note';
    public const CART_LABEL = 'cart_label';

    public function isPreorder(): ?bool
    {
        return $this->_get(self::PREORDER_FLAG);
    }

    public function setIsPreorder(bool $isPreorder): void
    {
        $this->setData(self::PREORDER_FLAG, $isPreorder);
    }

    public function getNote(): string
    {
        return (string)$this->_get(self::NOTE);
    }

    public function setNote(string $note): void
    {
        $this->setData(self::NOTE, $note);
    }

    public function getCartLabel(): string
    {
        return (string)$this->_get(self::CART_LABEL);
    }

    public function setCartLabel(string $cartLabel): void
    {
        $this->setData(self::CART_LABEL, $cartLabel);
    }
}
