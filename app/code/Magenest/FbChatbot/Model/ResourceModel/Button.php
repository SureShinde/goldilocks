<?php
namespace Magenest\FbChatbot\Model\ResourceModel;


class Button extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('magenest_chatbot_button', 'button_id');
    }

}
