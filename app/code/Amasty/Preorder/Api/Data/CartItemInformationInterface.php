<?php

namespace Amasty\Preorder\Api\Data;

interface CartItemInformationInterface
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
     * @return void
     */
    public function setNote(string $note): void;
}
