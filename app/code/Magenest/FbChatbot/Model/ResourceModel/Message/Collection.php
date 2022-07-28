<?php
namespace Magenest\FbChatbot\Model\ResourceModel\Message;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'message_id';
    protected $_eventPrefix = 'magenest_fbchatbot_message_collection';
    protected $_eventObject = 'message_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magenest\FbChatbot\Model\Message::class, \Magenest\FbChatbot\Model\ResourceModel\Message::class);
    }

}
