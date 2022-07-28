<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\Rule;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Magento\Framework\App\ScopeInterface;

interface RuleInterface
{
    /**
     * Returns is delivery date for order editable
     * @param DeliveryDateOrderInterface $deliveryDateOrder
     * @param int|ScopeInterface|null $store
     * @return bool
     */
    public function validate(DeliveryDateOrderInterface $deliveryDateOrder, $store = null): bool;
}
