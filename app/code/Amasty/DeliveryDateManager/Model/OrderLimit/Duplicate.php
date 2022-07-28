<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\OrderLimit;

use Amasty\DeliveryDateManager\Model\ModalDuplicateResolver\ResolverInterface;

class Duplicate implements ResolverInterface
{
    /**
     * @var Get
     */
    private $limitGetter;

    /**
     * @var Save
     */
    private $limitSaver;

    /**
     * @var LimitDataModelFactory
     */
    private $limitFactory;

    public function __construct(
        Get $limitGetter,
        Save $limitSaver,
        LimitDataModelFactory $limitFactory
    ) {
        $this->limitGetter = $limitGetter;
        $this->limitSaver = $limitSaver;
        $this->limitFactory = $limitFactory;
    }

    /**
     * @param int $limitId
     * @return int
     */
    public function execute(int $limitId): int
    {
        /** @var LimitDataModel $mainLimit */
        $mainLimit = $this->limitGetter->execute($limitId);

        /** @var LimitDataModel $newLimit */
        $newLimit = $this->limitFactory->create();
        $newLimit->setData($mainLimit->getData());
        $newLimit->setLimitId(null);
        $newLimit->setName('Copy of ' . $mainLimit->getName());
        $newLimit = $this->limitSaver->execute($newLimit);

        return $newLimit->getLimitId();
    }
}
