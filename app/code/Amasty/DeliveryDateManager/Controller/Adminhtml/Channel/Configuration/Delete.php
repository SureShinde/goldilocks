<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\Configuration;

use Amasty\DeliveryDateManager\Model\ChannelConfig\Delete as ChannelConfigDeleter;
use Amasty\DeliveryDateManager\Model\ChannelConfig\Get;
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
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::channel_configurations';

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
    private $channelConfigGetter;

    /**
     * @var ChannelConfigDeleter
     */
    private $channelConfigDeleter;

    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        Get $channelConfigGetter,
        ChannelConfigDeleter $channelConfigDeleter
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->channelConfigGetter = $channelConfigGetter;
        $this->channelConfigDeleter = $channelConfigDeleter;
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
                $channelConfig = $this->channelConfigGetter->execute($id);
                $this->channelConfigDeleter->execute($channelConfig);
                $message = __('You deleted the channel configuration.');
            } catch (\Exception $e) {
                $error = true;
                $message = __('We can\'t delete the channel configuration right now.');
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
