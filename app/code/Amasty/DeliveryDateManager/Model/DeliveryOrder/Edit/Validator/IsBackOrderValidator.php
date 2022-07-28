<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator;

use Amasty\DeliveryDateManager\Model\Validator\IsBackOrderInterface;

/**
 * Validate for backorders in the Order
 */
class IsBackOrderValidator implements IsBackOrderInterface
{
    /**
     * @return bool
     */
    public function execute(): bool
    {
        //For order we presume that backorder validation has no sense, because items was already ordered
        return false;
    }
}
