<?php

namespace Amasty\Preorder\Api\Data;

interface OrderInformationInterface
{
    public const MAIN_TABLE = 'amasty_preorder_order_preorder';
    public const ID = 'id';
    public const ORDER_ID = 'order_id';
    public const PREORDER_FLAG = 'is_preorder';
    public const WARNING = 'warning';

    /**
     * @return bool
     */
    public function isPreorder(): bool;

    /**
     * @param bool $isPreorder
     * @return OrderInformationInterface
     */
    public function setIsPreorder(bool $isPreorder): OrderInformationInterface;

    /**
     * @return string|null
     */
    public function getWarning(): ?string;

    /**
     * @param string $warning
     * @return OrderInformationInterface
     */
    public function setWarning(string $warning): OrderInformationInterface;
}
