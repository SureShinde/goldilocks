<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\RetrieveNote;

use Amasty\Preorder\Model\Product\RetrieveNote\DefaultValue\RetrieverInterface;
use InvalidArgumentException;

class DefaultValuePool
{
    /**
     * @var RetrieverInterface[]
     */
    private $pool;

    public function __construct(array $pool = [])
    {
        $this->pool = $pool;
    }

    public function getRetriever(string $code): RetrieverInterface
    {
        $retriever = $this->pool[$code] ?? null;
        if ($retriever === null) {
            throw new InvalidArgumentException(sprintf('Not found default value retriever for %s', $code));
        }

        return $retriever;
    }
}
