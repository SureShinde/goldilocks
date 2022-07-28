<?php

namespace Amasty\Preorder\Api\Data;

interface OrderItemInformationInterface
{
    public const MAIN_TABLE = 'amasty_preorder_order_item_preorder';
    public const ID = 'id';
    public const ORDER_ITEM_ID = 'order_item_id';
    public const PREORDER_FLAG = 'is_preorder';
    public const NOTE = 'preorder_note';

    /**
     * @return bool
     */
    public function isPreorder(): bool;

    /**
     * @param bool $isPreorder
     * @return OrderItemInformationInterface
     */
    public function setIsPreorder(bool $isPreorder): OrderItemInformationInterface;

    /**
     * @return string|null
     */
    public function getNote(): ?string;

    /**
     * @param string $note
     * @return OrderItemInformationInterface
     */
    public function setNote(string $note): OrderItemInformationInterface;
}
