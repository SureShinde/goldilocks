<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\OrderItemPreorder\Command;

use Amasty\Preorder\Api\Data\OrderItemInformationInterface;
use Amasty\Preorder\Model\OrderItemPreorder;
use Amasty\Preorder\Model\ResourceModel\OrderItemPreorder as OrderItemPreorderResource;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class Save implements SaveInterface
{
    /**
     * @var OrderItemPreorderResource
     */
    private $orderItemPreorderResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(OrderItemPreorderResource $orderItemPreorderResource, LoggerInterface $logger)
    {
        $this->orderItemPreorderResource = $orderItemPreorderResource;
        $this->logger = $logger;
    }

    /**
     * @param OrderItemInformationInterface|OrderItemPreorder $orderItemInformation
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(OrderItemInformationInterface $orderItemInformation): void
    {
        try {
            $this->orderItemPreorderResource->save($orderItemInformation);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Order Item Information'), $e);
        }
    }
}
