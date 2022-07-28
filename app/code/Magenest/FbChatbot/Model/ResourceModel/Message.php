<?php
namespace Magenest\FbChatbot\Model\ResourceModel;

class Message extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected $_serializableFields = ['message_types' => [[], []]];

    protected function _construct()
    {
        $this->_init('magenest_chatbot_message', 'message_id');
    }

}
