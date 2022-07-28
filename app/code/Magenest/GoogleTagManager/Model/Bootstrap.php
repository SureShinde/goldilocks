<?php

namespace Magenest\GoogleTagManager\Model;

use Magento\Framework\View\LayoutInterface;

class Bootstrap
{
    /**
     * @var \Magenest\GoogleTagManager\Model\Layout\Processor\Manipulator
     */
    private $processorManipulator;

    /**
     * @var string[][]
     */
    private $targetedHandleGroups;

    /**
     * @param \Magenest\GoogleTagManager\Model\Layout\Processor\Manipulator $processorManipulator
     * @param string[][] $targetedHandleGroups
     */
    public function __construct(
        \Magenest\GoogleTagManager\Model\Layout\Processor\Manipulator $processorManipulator,
        array $targetedHandleGroups = []
    ) {
        $this->processorManipulator = $processorManipulator;
        $this->targetedHandleGroups = $targetedHandleGroups;
    }

    public function bootstrapView($fullActionName, LayoutInterface $layout)
    {
        $update = $layout->getUpdate();

        foreach ($this->targetedHandleGroups as $name => $targets) {
            if (!isset($targets[$fullActionName]) || !$targets[$fullActionName]) {
                continue;
            }

            $this->processorManipulator->insertHandle(
                $update,
                $fullActionName,
                \sprintf('%s_%s', $name, $fullActionName)
            );
        }
    }
}
