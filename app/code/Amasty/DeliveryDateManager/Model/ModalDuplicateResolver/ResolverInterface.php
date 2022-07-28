<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ModalDuplicateResolver;

interface ResolverInterface
{
    /**
     * @param int $id
     * @return int
     */
    public function execute(int $id): int;
}
