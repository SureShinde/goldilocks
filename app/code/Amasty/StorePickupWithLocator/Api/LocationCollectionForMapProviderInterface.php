<?php

declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Api;

use Amasty\Storelocator\Model\ResourceModel\Location\Collection;

interface LocationCollectionForMapProviderInterface
{
    /**
     * @return Collection
     */
    public function getCollection(): Collection;
}
