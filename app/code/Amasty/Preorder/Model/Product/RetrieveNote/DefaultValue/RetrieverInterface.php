<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\RetrieveNote\DefaultValue;

interface RetrieverInterface
{
    public function execute(): string;
}
