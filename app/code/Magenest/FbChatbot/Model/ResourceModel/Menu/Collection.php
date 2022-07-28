<?php
namespace Magenest\FbChatbot\Model\ResourceModel\Menu;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'menu_id';
    protected $_eventPrefix = 'magenest_fbchatbot_menu_collection';
    protected $_eventObject = 'menu_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magenest\FbChatbot\Model\Menu::class, \Magenest\FbChatbot\Model\ResourceModel\Menu::class);
    }

}
