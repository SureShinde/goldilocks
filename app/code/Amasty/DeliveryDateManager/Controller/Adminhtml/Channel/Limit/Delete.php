<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\Limit;

use Amasty\DeliveryDateManager\Model\OrderLimit\Delete as OrderLimitDeleter;
use Amasty\DeliveryDateManager\Model\OrderLimit\Get;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::channel_limits';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Get
     */
    private $limitGetter;

    /**
     * @var OrderLimitDeleter
     */
    private $limitDeleter;

    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        Get $limitGetter,
        OrderLimitDeleter $limitDeleter
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->limitGetter = $limitGetter;
        $this->limitDeleter = $limitDeleter;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $message = '';
        $error = false;

        if ($id = (int)$this->getRequest()->getParam('id')) {
            try {
                $orderLimit = $this->limitGetter->execute($id);
                $this->limitDeleter->execute($orderLimit);
                $message = __('You deleted the order limit.');
            } catch (\Exception $e) {
                $error = true;
                $message = __('We can\'t delete the order limit right now.');
                $this->logger->critical($e);
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'message' => $message,
                'error' => $error,
            ]
        );

        return $resultJson;
    }
}
