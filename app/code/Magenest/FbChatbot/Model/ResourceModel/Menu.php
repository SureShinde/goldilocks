<?php
namespace Magenest\FbChatbot\Model\ResourceModel;

class Menu extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('magenest_chatbot_menu', 'menu_id');
    }
}
