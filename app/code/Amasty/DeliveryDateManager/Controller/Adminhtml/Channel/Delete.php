<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;

use Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;
use Amasty\DeliveryDateManager\Model\DeliveryChannel\Delete as ChannelDeleter;
use Amasty\DeliveryDateManager\Model\DeliveryChannel\Get;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Delete extends Channel
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::deliverydate_channel';

    /**
     * @var Get
     */
    private $channelGetter;

    /**
     * @var ChannelDeleter
     */
    private $channelDeleter;

    public function __construct(
        Context $context,
        Get $channelGetter,
        ChannelDeleter $channelDeleter
    ) {
        parent::__construct($context);
        $this->channelGetter = $channelGetter;
        $this->channelDeleter = $channelDeleter;
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($id = (int)$this->getRequest()->getParam('id')) {
            try {
                $channel = $this->channelGetter->execute($id);
                $this->channelDeleter->execute($channel);
                $this->messageManager->addSuccessMessage(__('You deleted the channel.'));
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }

        return $resultRedirect->setPath('amasty_deliverydate/channel');
    }
}
