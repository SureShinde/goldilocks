<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Validator;

/**
 * Validate for backorders
 */
interface IsBackOrderInterface
{
    /**
     * @return bool
     */
    public function execute(): bool;
}
