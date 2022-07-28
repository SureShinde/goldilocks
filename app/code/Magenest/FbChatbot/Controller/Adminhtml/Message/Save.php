<?php
namespace Magenest\FbChatbot\Controller\Adminhtml\Message;

use Magenest\FbChatbot\Api\Data\MessageInterface;
use Magenest\FbChatbot\Model\MessageFactory;
use Magenest\FbChatbot\Model\MessageRepository;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magenest\FbChatbot\Model\Bot;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Store\Model\StoreManagerInterface;
use Magenest\FbChatbot\Setup\Patch\Data\InsertNewMessageData;

class Save extends AbstractMessage
{
    /**
     * @var Bot
     */
    protected $bot;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param PageFactory $resultPageFactory
     * @param MessageRepository $messageRepository
     * @param MessageFactory $messageFactory
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param Bot $bot
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        PageFactory $resultPageFactory,
        MessageRepository $messageRepository,
        MessageFactory $messageFactory,
        Context $context, ForwardFactory $resultForwardFactory,
        Bot $bot,
        StoreManagerInterface $storeManager
    )
    {
        $this->bot = $bot;
        $this->storeManager = $storeManager;
        parent::__construct($resultPageFactory, $messageRepository, $messageFactory, $context, $resultForwardFactory);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        // action duplicate message
        if ($duplicateId = $this->getRequest()->getParam($this->_idField)) {
            try {
                $message = $this->messageRepository->getById($duplicateId);
                $data = $message->getData();
                unset($data[$this->_entityId]);
                unset($data[MessageInterface::MESSAGE_CODE]);
                $model = $this->messageFactory->create();
                $model->setData($data);
                $messageDup = $this->messageRepository->save($model);
                $this->messageManager->addSuccessMessage(__('Item was successfully duplicated.'));
                $resultRedirect->setPath('*/*/edit', ['id' => $messageDup->getId()]);
            } catch (NoSuchEntityException $noSuchEntityException) {
                $this->messageManager->addErrorMessage(__("Item that was requested doesn't exist. Verify item and try again."));
                $resultRedirect->setPath('*/*/index');
            } catch (AlreadyExistsException $e){
                $this->messageManager->addErrorMessage(__("Attribute Set already exists."));
                $resultRedirect->setPath('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Unable to proceed. Please, try again.'));
                $resultRedirect->setPath('*/*/index');
            }
            return $resultRedirect;
        } else if ($data = $this->getRequest()->getParams()) {
            if(empty($data[$this->_entityId])){
                unset($data[$this->_entityId]);
            }
            try {
                $message = isset($data[$this->_entityId]) ? $this->messageRepository->getById((int)$data[$this->_entityId]) : $this->messageFactory->create();
                if (isset($data[MessageInterface::MESSAGE_TYPES])) {

                    foreach ($data[MessageInterface::MESSAGE_TYPES][InsertNewMessageData::OPTIONS] as $key => $item) {
                        // validation for message have code is show_product_buttons
                        if (isset($data[MessageInterface::MESSAGE_CODE]) && $data[MessageInterface::MESSAGE_CODE] == InsertNewMessageData::SHOW_PRODUCT_BUTTONS_CODE) {
                            if ($item[InsertNewMessageData::MESSAGE_TYPE] != 1 || count($data[MessageInterface::MESSAGE_TYPES][InsertNewMessageData::OPTIONS]) > 1) {
                                $this->messageManager->addErrorMessage(__("Invalid message content. Please try again!"));
                                return $resultRedirect->setPath('*/*/edit', ['id' => $message->getId()]);
                            }
                        }
                        // validation included button
                        if ($item[InsertNewMessageData::INCLUDE_BUTTON] == "1" && !isset($item[InsertNewMessageData::VALUES])) {
                            $this->messageManager->addErrorMessage(__("You selected to include button but no button exists"));
                            return $resultRedirect->setPath('*/*/edit', ['id' => $message->getId()]);
                        }
                        // upload image to facebook to get attachment_id
                        if (isset($item['image'][0]['url'])) {
                            $urlImage = $item['image'][0]['url'];
                            // get url image from media gallery
                            if (!isset($item['image'][0]['image_from_upload'])) {
                                $urlImage = $this->storeManager->getStore()->getBaseUrl() . substr($urlImage, 1);
                            }
                            $attachment = $this->bot->uploadAttachment($urlImage);
                            if (empty($attachment['error'])) {
                                $data[MessageInterface::MESSAGE_TYPES][InsertNewMessageData::OPTIONS][$key]['image'][0]['attachment_id'] = $attachment['attachment_id'];
                            }
                        }
                    }
                } else {
                    $this->messageManager->addErrorMessage(__("The message content must have an entry"));
                    return $resultRedirect->setPath('*/*/edit', ['id' => $message->getId()]);
                }
                $message->setData($data);
                $this->messageRepository->save($message);
                $this->messageManager->addSuccessMessage(__('Item was successfully saved'));
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('*/*/edit', ['id' => $message->getId()]);
                } else {
                    $resultRedirect->setPath('*/*/index');
                }
            } catch (NoSuchEntityException $noSuchEntityException) {
                $this->messageManager->addErrorMessage(__("Item that was requested doesn't exist. Verify item and try again."));
                $resultRedirect->setPath('*/*/index');
            } catch (AlreadyExistsException $e){
                $this->messageManager->addErrorMessage(__("Attribute Set already exists."));
                $resultRedirect->setPath('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('*/*/index');
            }
            return $resultRedirect;
        } else {
            $this->messageManager->addErrorMessage(__('Unable to find item to save'));
            return $resultRedirect->setPath('*/*/');
        }
    }
}
