<?php
namespace Magenest\FbChatbot\Api\Data;


interface MenuInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    const ID = 'menu_id';
    const ACTIVE = 'is_active';
    const DESCRIPTION = 'description';
    const NAME = 'name';
    const MESSAGE_ID = 'message_id';

    public function getId();

    public function setId($id);

    public function getActive();

    public function setActive($isActive);

    public function getDescription();

    public function setDescription($description);

    public function getName();

    public function setName($name);

    public function getMessageId();

    public function setMessageId($messageId);

}
