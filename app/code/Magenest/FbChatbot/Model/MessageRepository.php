<?php

namespace Magenest\FbChatbot\Model;

use Magenest\FbChatbot\Api\Data\MessageInterface;
use Magenest\FbChatbot\Api\MessageRepositoryInterface;
use Magenest\FbChatbot\Model\ResourceModel\Message\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;

class MessageRepository implements MessageRepositoryInterface
{
    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var Button[]
     */
    protected $instancesById = [];

    /**
     * @var Button[]
     */
    protected $instances = [];

    /**
     * @var int
     */
    private $cacheLimit = 0;

    /**
     * @var MessageFactory
     */
    protected $_messageFactory;

    /**
     * @var ResourceModel\Message
     */
    protected $_messageResource;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * MessageRepository constructor.
     * @param MessageFactory $messageFactory
     * @param ResourceModel\Message $messageResource
     * @param CollectionFactory $collectionFactory
     * @param int $cacheLimit
     * @param Json|null $serializer
     */
    public function __construct(
        MessageFactory $messageFactory,
        \Magenest\FbChatbot\Model\ResourceModel\Message $messageResource,
        CollectionFactory $collectionFactory,
        $cacheLimit = 1000,
        Json $serializer = null
    ) {
        $this->_messageFactory = $messageFactory;
        $this->_messageResource = $messageResource;
        $this->collectionFactory = $collectionFactory;
        $this->cacheLimit = (int)$cacheLimit;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * @param $name
     * @return mixed|null
     * @throws NoSuchEntityException
     */
    public function get($name){
        $messageId = $this->collectionFactory->create()
            ->addFieldToFilter(Message::NAME, $name)
            ->getLastItem()
            ->getId();
        if (!$messageId){
            return $this->getByCode(Message::DEFAULT_MESSAGE_CODE);
        }
        return $this->getById($messageId);
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(MessageInterface $message)
    {
        $this->_messageResource->save($message);
        $this->removeMessageFromLocalCacheById($message->getId());
        if ($message->getCode()){
            $this->removeMessageFromLocalCacheByCode($message->getCode());
        }
        return $message;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function getById($messageId)
    {
        $cacheKey = $this->getCacheKey([]);
        if (!isset($this->instancesById[$messageId][$cacheKey])) {
            $message = $this->_messageFactory->create();
            $this->_messageResource->load($message,$messageId);
            if (!$message->getId()) {
                throw new NoSuchEntityException(
                    __("The message that was requested doesn't exist. Verify the message and try again.")
                );
            }
            $this->cacheMessage($cacheKey, $message);
        }
        return $this->instancesById[$messageId][$cacheKey];
    }

    /**
     * @param $messageCode
     * @return mixed|null
     * @throws NoSuchEntityException
     */
    public function getByCode($messageCode)
    {
        $cacheKey = $this->getCacheKey([]);
        $cachedMessage = $this->getMessageFromLocalCache($messageCode, $cacheKey);
        if ($cachedMessage === null) {
            $message = $this->_messageFactory->create();
            $this->_messageResource->load($message,$messageCode, 'code');
            if (!$message->getId()) {
                throw new NoSuchEntityException(
                    __("The message that was requested doesn't exist. Verify the message and try again.")
                );
            }
            $this->cacheMessage($cacheKey, $message);
            $cachedMessage = $message;
        }
        return $cachedMessage;
    }

    /**
     * @param string $code
     * @param string $cacheKey
     * @return mixed|null
     */
    private function getMessageFromLocalCache(string $code, string $cacheKey)
    {
        $preparedSku = $this->prepareCode($code);

        return $this->instances[$preparedSku][$cacheKey] ?? null;
    }

    /**
     * @param string $code
     * @return string
     */
    private function prepareCode(string $code): string
    {
        return mb_strtolower(trim($code));
    }

    /**
     * @param $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }
        $serializeData = $this->serializer->serialize($serializeData);
        return sha1($serializeData);
    }

    /**
     * @param $cacheKey
     * @param MessageInterface $message
     */
    private function cacheMessage($cacheKey, MessageInterface $message)
    {
        $this->instancesById[$message->getId()][$cacheKey] = $message;
        $this->saveMessageInLocalCache($message, $cacheKey);

        if ($this->cacheLimit && count($this->instances) > $this->cacheLimit) {
            $offset = round($this->cacheLimit / -2);
            $this->instancesById = array_slice($this->instancesById, $offset, null, true);
            $this->instances = array_slice($this->instances, $offset, null, true);
        }
    }

    /**
     * @param MessageInterface $message
     * @param $cacheKey
     */
    private function saveMessageInLocalCache(MessageInterface $message, $cacheKey)
    {
        $preparedCode = $this->prepareId($message->getId());
        $this->instances[$preparedCode][$cacheKey] = $message;
    }

    /**
     * Removes message in the local cache by code.
     *
     * @param string $code
     * @return void
     */
    private function removeMessageFromLocalCacheByCode(string $code): void
    {
        $preparedCode = $this->prepareCode($code);
        unset($this->instances[$preparedCode]);
    }

    /**
     * Removes message in the local cache by id.
     *
     * @param string|null $id
     * @return void
     */
    private function removeMessageFromLocalCacheById(?string $id): void
    {
        unset($this->instancesById[$id]);
    }

    /**
     * @param $id
     * @return string
     */
    private function prepareId($id)
    {
        return (string) trim($id);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function delete(MessageInterface $message)
    {
        $this->_messageResource->delete($message);
        $this->removeMessageFromLocalCacheById($message->getId());
        if ($message->getCode()){
            $this->removeMessageFromLocalCacheByCode($message->getCode());
        }
        return true;

    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function deleteById($messageId)
    {
        $message = $this->getById($messageId);
        return $this->delete($message);
    }

}
