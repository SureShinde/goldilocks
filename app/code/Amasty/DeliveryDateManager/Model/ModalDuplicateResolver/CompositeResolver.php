<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ModalDuplicateResolver;

use Magento\Framework\Exception\LocalizedException;

class CompositeResolver
{
    /**
     * @var ResolverInterface[]
     */
    private $resolvers;

    public function __construct(
        array $resolvers = []
    ) {
        $this->resolvers = $resolvers;
    }

    /**
     * @param int $id
     * @param string $type
     * @return int
     * @throws LocalizedException
     */
    public function execute(int $id, string $type): int
    {
        if (isset($this->resolvers[$type])) {
            if ($this->resolvers[$type] instanceof ResolverInterface) {
                return $this->resolvers[$type]->execute($id);
            } else {
                throw new \InvalidArgumentException(
                    'Type "' . get_class($this->resolvers[$type]) . '" is not instance on ' . ResolverInterface::class
                );
            }
        }
        throw new LocalizedException(__('The wrong resolver type.'));
    }
}
