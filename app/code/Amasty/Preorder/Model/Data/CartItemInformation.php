<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Data;

use Amasty\Preorder\Api\Data\CartItemInformationInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class CartItemInformation extends AbstractSimpleObject implements CartItemInformationInterface
{
    public const PREORDER_FLAG = 'preorder_flag';
    public const NOTE = 'note';

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
        return $this->_get(self::NOTE);
    }

    public function setNote(string $note): void
    {
        $this->setData(self::NOTE, $note);
    }
}
