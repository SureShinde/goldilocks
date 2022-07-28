<?php

namespace Magenest\FbChatbot\Controller\Adminhtml\Menu;

use Magenest\FbChatbot\Api\MessageRepositoryInterface;
use Magenest\FbChatbot\Model\Bot;
use Magenest\FbChatbot\Model\Menu;
use Magenest\FbChatbot\Model\MenuFactory;
use Magenest\FbChatbot\Model\MenuRepository;
use Magenest\FbChatbot\Model\ResourceModel\Menu\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;

abstract class AbstractMenu extends Action
{
    /**
     * @var string
     */
    const ADMIN_RESOURCE = 'Magenest_FbChatbot::menu';

    protected $_entityId = 'menu_id';

    protected $_idField = 'id';


    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Bot
     */
    protected $bot;
    /**
     * @var MenuFactory
     */
    protected $menuFactory;

    /**
     * @var CollectionFactory
     */
    protected $menuColFactory;

    /**
     * @var MessageRepositoryInterface
     */
    protected $messageRepository;

    /**
     * @var MenuRepository
     */
    protected $menuRepository;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * AbstractMenu constructor.
     * @param PageFactory $resultPageFactory
     * @param Bot $bot
     * @param Context $context
     * @param MenuRepository $menuRepository
     * @param MenuFactory $menuFactory
     * @param CollectionFactory $menuColFactory
     * @param MessageRepositoryInterface $messageRepository
     */
    public function __construct(
        PageFactory $resultPageFactory,
        Bot $bot,
        Context $context,
        MenuRepository $menuRepository,
        MenuFactory $menuFactory,
        CollectionFactory $menuColFactory,
        MessageRepositoryInterface $messageRepository
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->bot = $bot;
        $this->menuRepository = $menuRepository;
        $this->menuFactory = $menuFactory;
        $this->menuColFactory = $menuColFactory;
        $this->messageRepository = $messageRepository;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function canDelete(){
        return !in_array($this->getRequest()->getParam($this->_idField), Menu::MENU_CANNOT_DELETE);
    }


}
