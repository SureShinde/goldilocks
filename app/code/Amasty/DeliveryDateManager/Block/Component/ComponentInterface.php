<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Component;

interface ComponentInterface
{
    /**
     * @return string
     */
    public function getName(): string;
    
    /**
     * @param int $storeId
     * @return array
     */
    public function getComponent(int $storeId): array;

    /**
     * @param int $storeId
     * @return bool
     */
    public function isEnabled(int $storeId): bool;
}
