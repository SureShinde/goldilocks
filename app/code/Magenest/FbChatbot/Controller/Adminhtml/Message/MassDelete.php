<?php
namespace Magenest\FbChatbot\Controller\Adminhtml\Message;

use Magenest\FbChatbot\Api\MessageRepositoryInterface;
use Magenest\FbChatbot\Model\Message;
use Magenest\FbChatbot\Model\ResourceModel\Message\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var MessageRepositoryInterface
     */
    protected $messageRepository;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param MessageRepositoryInterface $messageRepository
     */
    public function __construct(
        Context $context, Filter $filter,
        CollectionFactory $collectionFactory,
        MessageRepositoryInterface $messageRepository
    ){
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->messageRepository = $messageRepository;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $count = 0;
        foreach ($collection as $item) {
            if (!in_array($item->getCode(),Message::MESSAGE_CANNOT_DELETE)){
                $this->messageRepository->deleteById($item->getId());
                $count++;
            }
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $count));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
