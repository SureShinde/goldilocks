<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;

use Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;
use Amasty\DeliveryDateManager\Model\DeliveryChannel\Duplicate as ChannelDuplicator;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Duplicate extends Channel
{
    /**
     * @var ChannelDuplicator
     */
    private $channelDuplicator;

    public function __construct(
        Context $context,
        ChannelDuplicator $channelDuplicator
    ) {
        parent::__construct($context);
        $this->channelDuplicator = $channelDuplicator;
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($channelId = (int)$this->getRequest()->getParam('id')) {
            try {
                $duplicatedChannel = $this->channelDuplicator->execute($channelId);

                return $resultRedirect->setPath('*/*/edit', ['id' => $duplicatedChannel->getChannelId()]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['id' => $channelId]);
            }
        }

        return $resultRedirect->setPath('*/*/new');
    }
}
