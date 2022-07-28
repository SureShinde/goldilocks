<?php

declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Api;

interface LocationSourceManagementInterface
{
    /**
     * @param int $productId
     * @return \Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceSearchResultInterface
     */
    public function getLocationsByProduct(
        int $productId
    ): \Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceSearchResultInterface;
}
