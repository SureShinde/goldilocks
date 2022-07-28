<?php

namespace Amasty\Preorder\Api\Data;

/**
 * Product preorder data
 *
 * @api
 */
interface ProductInformationInterface
{
    /**
     * @return bool|null
     */
    public function isPreorder(): ?bool;

    /**
     * @param bool $isPreorder
     * @return void
     */
    public function setIsPreorder(bool $isPreorder): void;

    /**
     * @return string
     */
    public function getNote(): string;

    /**
     * @param string $note
     * @return ProductInformationInterface
     */
    public function setNote(string $note): void;

    /**
     * @return string
     */
    public function getCartLabel(): string;

    /**
     * @param string $cartLabel
     * @return void
     */
    public function setCartLabel(string $cartLabel): void;
}
