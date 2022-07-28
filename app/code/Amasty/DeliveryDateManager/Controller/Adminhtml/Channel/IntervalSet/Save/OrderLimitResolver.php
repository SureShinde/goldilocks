<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\IntervalSet\Save;

use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Model\OrderLimit\Delete as OrderLimitDelete;
use Amasty\DeliveryDateManager\Model\OrderLimit\Get as OrderLimitGet;
use Amasty\DeliveryDateManager\Model\OrderLimit\LimitDataModelFactory;
use Amasty\DeliveryDateManager\Model\OrderLimit\Save as OrderLimitSave;

class OrderLimitResolver
{
    /**
     * @var LimitDataModelFactory
     */
    private $orderLimitFactory;

    /**
     * @var OrderLimitGet
     */
    private $orderLimitGetter;

    /**
     * @var OrderLimitSave
     */
    private $orderLimitSaver;

    /**
     * @var OrderLimitDelete
     */
    private $orderLimitDeleter;

    public function __construct(
        LimitDataModelFactory $orderLimitFactory,
        OrderLimitGet $orderLimitGetter,
        OrderLimitSave $orderLimitSaver,
        OrderLimitDelete $orderLimitDeleter
    ) {
        $this->orderLimitFactory = $orderLimitFactory;
        $this->orderLimitGetter = $orderLimitGetter;
        $this->orderLimitSaver = $orderLimitSaver;
        $this->orderLimitDeleter = $orderLimitDeleter;
    }

    /**
     * @param array $timeData
     * @return int|null
     */
    public function execute(array $timeData): ?int
    {
        $intervalLimit = $timeData[OrderLimitInterface::INTERVAL_LIMIT] ?? '';

        if (empty($timeData[OrderLimitInterface::LIMIT_ID])) {
            $limit = $this->orderLimitFactory->create();
        } else {
            $limit = $this->orderLimitGetter->execute((int)$timeData[OrderLimitInterface::LIMIT_ID]);
        }

        if (empty($intervalLimit)
            && ($intervalLimit !== '0')
            && $limit->getLimitId()
        ) {
            $this->orderLimitDeleter->execute($limit);
        } elseif (!empty($intervalLimit) || ($intervalLimit !== '')) {
            $limit->setIntervalLimit((int)$intervalLimit);
            $this->orderLimitSaver->execute($limit);

            return $limit->getLimitId();
        }

        return null;
    }
}
