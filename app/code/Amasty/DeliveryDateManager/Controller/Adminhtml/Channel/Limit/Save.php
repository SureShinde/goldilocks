<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\Limit;

use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Model\OrderLimit\LimitDataModelFactory;
use Amasty\DeliveryDateManager\Model\OrderLimit\Save as SaveOrderLimit;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::channel_limits';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SaveOrderLimit
     */
    private $saveOrderLimit;

    /**
     * @var LimitDataModelFactory
     */
    private $limitDataModelFactory;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        SaveOrderLimit $saveOrderLimit,
        LimitDataModelFactory $limitDataModelFactory
    ) {
        $this->logger = $logger;
        $this->saveOrderLimit = $saveOrderLimit;
        $this->limitDataModelFactory = $limitDataModelFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON);
        }

        try {
            $orderLimitData = $this->getRequest()->getParams();

            /** @var OrderLimitInterface $orderLimit */
            $orderLimit = $this->limitDataModelFactory->create();
            $orderLimit->setData($orderLimitData);
            $this->saveOrderLimit->execute($orderLimit);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

        $closeAction = true;
        if ($this->getRequest()->getParam('back', false)) {
            $closeAction = false;
        }

        return $this->resultFactory
            ->create(ResultFactory::TYPE_JSON)
            ->setData(
                [
                    'closeAction' => $closeAction,
                    'status' => 'done',
                    'id' => $orderLimit->getLimitId()
                ]
            );
    }
}
