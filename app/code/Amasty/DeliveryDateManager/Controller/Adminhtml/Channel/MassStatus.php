<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;

use Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;
use Amasty\DeliveryDateManager\Model\DeliveryChannel\DeliveryChannelData;
use Amasty\DeliveryDateManager\Model\DeliveryChannel\Save as DeliveryChannelSaver;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassStatus extends Channel implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::deliverydate_channel';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DeliveryChannelSaver
     */
    private $deliveryChannelSaver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        DeliveryChannelSaver $deliveryChannelSaver,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->deliveryChannelSaver = $deliveryChannelSaver;
        $this->logger = $logger;
    }

    /**
     * Mass update status action
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $status = $this->getRequest()->getParam('status', null);
        $updatedCount = 0;

        if ($status !== null) {
            /** @var DeliveryChannelData $channel */
            foreach ($collection->getItems() as $channel) {
                try {
                    $channel->setIsActive((bool)$status);
                    $channel->setSimpleUpdate();

                    $this->deliveryChannelSaver->execute($channel);
                    $updatedCount++;
                } catch (LocalizedException $exception) {
                    $this->logger->error($exception->getLogMessage());
                }
            }
        }

        if ($updatedCount) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been updated.', $updatedCount)
            );
        } else {
            $this->messageManager->addErrorMessage(
                __('No record(s) have been updated.', $updatedCount)
            );
        }

        return $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT)
            ->setPath('*/*/index');
    }
}
