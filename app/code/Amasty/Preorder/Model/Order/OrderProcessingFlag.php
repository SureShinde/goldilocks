<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order;

class OrderProcessingFlag
{
    /**
     * @var bool
     */
    private $flag = false;

    public function isFlag(): bool
    {
        return $this->flag;
    }

    public function setFlag(bool $flag): void
    {
        $this->flag = $flag;
    }
}
