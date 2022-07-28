<?php

namespace Magenest\FbChatbot\Model;

use Magento\Framework\DataObject\IdentityInterface;

class Menu extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, \Magenest\FbChatbot\Api\Data\MenuInterface
{
    const MENU_CANNOT_DELETE = [1];

    const CACHE_TAG = 'magenest_fbchatbot_menu';

    protected $_cacheTag = 'magenest_fbchatbot_menu';

    protected $_eventPrefix = 'magenest_fbchatbot_menu';

    protected function _construct()
    {
        $this->_init(\Magenest\FbChatbot\Model\ResourceModel\Menu::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }

    public function getCustomAttribute($attributeCode)
    {
        // TODO: Implement getCustomAttribute() method.
    }

    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        // TODO: Implement setCustomAttribute() method.
    }

    public function getCustomAttributes()
    {
        // TODO: Implement getCustomAttributes() method.
    }

    public function setCustomAttributes(array $attributes)
    {
        // TODO: Implement setCustomAttributes() method.
    }

    public function getId()
    {
        return $this->getData(self::ID);
    }

    public function setId($id)
    {
        $this->setData(self::ID,$id);
        return $this;
    }

    public function getActive()
    {
        return $this->getData(self::ACTIVE);
    }

    public function setActive($isActive)
    {
        $this->setData(self::ACTIVE,$isActive);
        return $this;
    }

    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    public function setDescription($description)
    {
        $this->setData(self::DESCRIPTION,$description);
        return $this;
    }

    public function getName()
    {
        return $this->getData(self::NAME);
    }

    public function setName($name)
    {
        $this->setData(self::NAME,$name);
        return $this;
    }

    public function getMessageId()
    {
        return $this->getData(self::MESSAGE_ID);
    }

    public function setMessageId($messageId)
    {
        $this->setData(self::MESSAGE_ID,$messageId);
        return $this;
    }
}
