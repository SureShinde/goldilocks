<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\Configuration;

use Amasty\DeliveryDateManager\Model\ChannelConfig\ConfigDataFactory;
use Amasty\DeliveryDateManager\Model\ChannelConfig\Save as SaveChannelConfig;
use Amasty\DeliveryDateManager\Model\Preprocessor\CompositePreprocessor;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::channel_configurations';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SaveChannelConfig
     */
    private $saveChannelConfig;

    /**
     * @var ConfigDataFactory
     */
    private $channelConfigDataFactory;

    /**
     * @var CompositePreprocessor
     */
    private $dataPreprocessor;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        SaveChannelConfig $saveChannelConfig,
        ConfigDataFactory $channelConfigDataFactory,
        CompositePreprocessor $dataPreprocessor
    ) {
        $this->logger = $logger;
        $this->saveChannelConfig = $saveChannelConfig;
        $this->channelConfigDataFactory = $channelConfigDataFactory;
        $this->dataPreprocessor = $dataPreprocessor;
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
            $channelConfig = $this->channelConfigDataFactory->create();
            $channelConfigData = $this->getRequest()->getParams();

            $this->dataPreprocessor->process($channelConfigData);
            $channelConfig->setData($channelConfigData);
            $this->saveChannelConfig->execute($channelConfig);
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
                    'id' => $channelConfig->getId()
                ]
            );
    }
}
