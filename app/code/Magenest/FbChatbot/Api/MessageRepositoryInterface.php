<?php
namespace Magenest\FbChatbot\Api;

use Magenest\FbChatbot\Api\Data\MessageInterface;

interface MessageRepositoryInterface
{
    /**
     * @param $name
     * @return mixed
     */
    public function get($name);

    /**
     * @param MessageInterface $message
     * @return mixed
     */
    public function save(MessageInterface $message);

    /**
     * @param $messageId
     * @return mixed
     */
    public function getById($messageId);

    /**
     * @param $messageCode
     * @return mixed
     */
    public function getByCode($messageCode);

    /**
     * @param MessageInterface $message
     * @return mixed
     */
    public function delete(MessageInterface $message);

    /**
     * @param $messageId
     * @return mixed
     */
    public function deleteById($messageId);
}
