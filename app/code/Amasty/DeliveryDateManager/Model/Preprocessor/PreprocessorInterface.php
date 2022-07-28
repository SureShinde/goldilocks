<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Preprocessor;

interface PreprocessorInterface
{
    /**
     * @param array &$data
     */
    public function process(array &$data): void;
}
