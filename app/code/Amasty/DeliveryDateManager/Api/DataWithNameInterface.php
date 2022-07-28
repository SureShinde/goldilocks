<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api;

/**
 * Name column is only for admin usage.
 * Name shouldn't outputs to storefront.
 */
interface DataWithNameInterface
{
    public const NAME = 'name';

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void;
}
