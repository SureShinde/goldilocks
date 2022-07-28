<?php
namespace Magenest\FbChatbot\Controller\Adminhtml\Message;

use Magenest\FbChatbot\Model\Message;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magenest\FbChatbot\Model\MessageRepository as MessageRepository;
use Magenest\FbChatbot\Model\MessageFactory as MessageFactory;

abstract class AbstractMessage extends Action
{
    /**
     * @var string
     */
    const ADMIN_RESOURCE = 'Magenest_FbChatbot::message';

    protected $_entityId = 'message_id';

    protected $_idField = 'id';


    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var MessageRepository
     */
    protected $messageRepository;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * AbstractMessage constructor.
     * @param PageFactory $resultPageFactory
     * @param MessageRepository $messageRepository
     * @param MessageFactory $messageFactory
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        PageFactory $resultPageFactory,
        MessageRepository $messageRepository,
        MessageFactory $messageFactory,
        Context $context,
        ForwardFactory $resultForwardFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->messageRepository = $messageRepository;
        $this->messageFactory = $messageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function canDelete(){
        return !in_array($this->getRequest()->getParam($this->_idField), Message::MESSAGE_CANNOT_DELETE);
    }
}
