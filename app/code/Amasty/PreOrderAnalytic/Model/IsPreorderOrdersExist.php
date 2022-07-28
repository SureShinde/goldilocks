<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model;

use Amasty\Preorder\Model\ResourceModel\OrderPreorder\Collection;
use Amasty\Preorder\Model\ResourceModel\OrderPreorder\CollectionFactory;

class IsPreorderOrdersExist
{
    /**
     * @var bool|null
     */
    private $isExist;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function execute(): bool
    {
        if ($this->isExist === null) {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $this->isExist = (bool) $collection->getSize();
        }

        return $this->isExist;
    }
}
