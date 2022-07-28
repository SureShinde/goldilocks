<?php

namespace Magenest\GoogleTagManager\Model\Layout\Processor;

use Magento\Framework\View\Layout\ProcessorInterface;

class Manipulator
{
    public function insertHandle(ProcessorInterface $processor, $target, $handle)
    {
        $handles = $processor->getHandles();
        $insertionIndex = \array_search($target, $handles);

        if ($insertionIndex) {
            \array_splice($handles, $insertionIndex ? $insertionIndex + 1 : \count($handles), 0, $handle);
        }

        \array_map([$processor, 'removeHandle'], $processor->getHandles());
        \array_map([$processor, 'addHandle'], $handles);
    }
}
