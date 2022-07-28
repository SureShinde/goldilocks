<?php
namespace Magenest\FbChatbot\Block\Adminhtml\Message\Edit;

use Magenest\FbChatbot\Api\MessageRepositoryInterface;
use Magenest\FbChatbot\Model\Message;
use Magento\Framework\Exception\NoSuchEntityException;

class GenericButton
{
    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var MessageRepositoryInterface
     */
    protected $messageRepository;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        MessageRepositoryInterface $messageRepository
    ) {
        $this->context = $context;
        $this->messageRepository = $messageRepository;
    }

    /**
     * Return the current sales Message Id.
     *
     * @return int|null
     */
    public function getMessageId()
    {
        try {
            $message = $this->messageRepository->getById($this->context->getRequest()->getParam('id'));
            if (!in_array($message->getCode(),Message::MESSAGE_CANNOT_DELETE))
                return $message->getId();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Generate url by route and parameters
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

}
