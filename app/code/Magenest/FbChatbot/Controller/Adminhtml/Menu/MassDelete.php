<?php
namespace Magenest\FbChatbot\Controller\Adminhtml\Menu;

use Magenest\FbChatbot\Api\MenuRepositoryInterface;
use Magenest\FbChatbot\Model\Menu;
use Magenest\FbChatbot\Model\ResourceModel\Menu\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magenest\FbChatbot\Model\Bot;

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
     * @var MenuRepositoryInterface
     */
    protected $menuRepository;
    /**
     * @var Bot
     */
    protected $bot;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param MenuRepositoryInterface $menuRepository
     * @param Bot $bot
     */
    public function __construct(
        Context $context, Filter $filter,
        CollectionFactory $collectionFactory,
        MenuRepositoryInterface $menuRepository,
        Bot $bot
    ){
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->menuRepository = $menuRepository;
        $this->bot = $bot;
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
            if (!in_array($item->getId(),Menu::MENU_CANNOT_DELETE)){
                $this->menuRepository->deleteById($item->getId());
                $count++;
            }
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $count));
        if($count) {
            $this->bot->setupPersistentMenu();
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
