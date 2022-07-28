<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterfaceFactory;
use Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;
use Amasty\DeliveryDateManager\Model\DeliveryChannel\Save as DeliveryChannelSaver;
use Amasty\DeliveryDateManager\Model\Preprocessor\CompositePreprocessor;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Save extends Channel
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::deliverydate_channel';

    /**
     * @var DeliveryChannelInterfaceFactory
     */
    private $channelInterfaceFactory;

    /**
     * @var DeliveryChannelSaver
     */
    private $deliveryChannelSaver;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var LoggerInterface
     */
    private $logInterface;

    /**
     * @var CompositePreprocessor
     */
    private $dataPreprocessor;

    public function __construct(
        Context $context,
        DeliveryChannelInterfaceFactory $channelInterfaceFactory,
        DeliveryChannelSaver $deliveryChannelSaver,
        Session $session,
        LoggerInterface $logInterface,
        CompositePreprocessor $dataPreprocessor
    ) {
        parent::__construct($context);
        $this->channelInterfaceFactory = $channelInterfaceFactory;
        $this->deliveryChannelSaver = $deliveryChannelSaver;
        $this->session = $session;
        $this->logInterface = $logInterface;
        $this->dataPreprocessor = $dataPreprocessor;
    }

    /**
     * Save Configuration Action
     *
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->getRequest()->getPostValue()) {
            $data = $this->getRequest()->getPostValue();
            $channelId = $this->getRequest()->getParam('channel_id');

            try {
                if (is_array($data) && !empty($data)) {
                    /** @var \Amasty\DeliveryDateManager\Model\DeliveryChannel\DeliveryChannelData $channelModel */
                    $channelModel = $this->channelInterfaceFactory->create();
                    $this->dataPreprocessor->process($data);
                    $channelModel->setData($data);
                    $this->deliveryChannelSaver->execute($channelModel);
                    $this->messageManager->addSuccessMessage(__('Configuration has been successfully saved'));

                    if ($this->getRequest()->getParam('back', false)) {
                        return $resultRedirect
                            ->setPath('amasty_deliverydate/channel/edit', ['id' => $channelModel->getChannelId()]);
                    }
                } else {
                    throw new LocalizedException(__('The wrong configuration is specified.'));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('amasty_deliverydate/channel/edit', ['id' => $channelId]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving data. Please review the error log.')
                );
                $this->logInterface->critical($e);
                $this->session->setPageData($data);

                return $resultRedirect->setPath('amasty_deliverydate/channel/edit', ['id' => $channelId]);
            }
        }

        return $resultRedirect->setPath('amasty_deliverydate/channel');
    }
}
