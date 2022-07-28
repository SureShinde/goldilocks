<?php
namespace Magenest\FbChatbot\Model\ResourceModel\Button;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'button_id';
    protected $_eventPrefix = 'magenest_fbchatbot_button_collection';
    protected $_eventObject = 'button_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magenest\FbChatbot\Model\Button::class, \Magenest\FbChatbot\Model\ResourceModel\Button::class);
    }

}
