<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\OrderPreorder\Command;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Amasty\Preorder\Model\OrderPreorder;
use Amasty\Preorder\Model\ResourceModel\OrderPreorder as OrderPreorderResource;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class Save implements SaveInterface
{
    /**
     * @var OrderPreorderResource
     */
    private $orderPreorderResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(OrderPreorderResource $orderPreorderResource, LoggerInterface $logger)
    {
        $this->orderPreorderResource = $orderPreorderResource;
        $this->logger = $logger;
    }

    /**
     * @param OrderInformationInterface|OrderPreorder $orderInformation
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(OrderInformationInterface $orderInformation): void
    {
        try {
            $this->orderPreorderResource->save($orderInformation);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Order Item Information'), $e);
        }
    }
}
