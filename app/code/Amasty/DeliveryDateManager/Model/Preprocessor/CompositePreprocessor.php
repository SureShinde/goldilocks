<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Preprocessor;

class CompositePreprocessor implements PreprocessorInterface
{
    /**
     * @var PreprocessorInterface[]
     */
    private $processors;

    public function __construct(
        array $processors = []
    ) {
        $this->processors = $processors;
    }

    /**
     * @param array &$data
     */
    public function process(array &$data): void
    {
        foreach ($this->processors as $processor) {
            if ($processor instanceof PreprocessorInterface) {
                $processor->process($data);
            } else {
                throw new \InvalidArgumentException(
                    'Type "' . get_class($processor) . '" is not instance on ' . PreprocessorInterface::class
                );
            }
        }
    }
}
