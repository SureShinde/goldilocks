<?php

namespace Magenest\AdvancedLogin\Model\Attribute;

use Magenest\AdvancedLogin\Helper\Login;
use Magenest\AdvancedLogin\Model\ConfigProvider;
use Magento\Framework\Exception\NoSuchEntityException;

class Telephone extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /** @var ConfigProvider */
    protected $_configProvider;

    /**
     * Telephone constructor.
     * @param ConfigProvider $configProvider
     */
    public function __construct(ConfigProvider $configProvider)
    {
        $this->_configProvider = $configProvider;
    }

    /**
     * @param $object
     * @throws NoSuchEntityException
     */
    protected function checkUniqueTelephone($object)
    {
        $attribute = $this->getAttribute();
        $entity = $attribute->getEntity();
        $value = $object->getData($attribute->getAttributeCode());
        if (preg_match(Login::REGEX_MOBILE_NUMBER, $value)) {
            $object->setData($attribute->getAttributeCode(), $value);
        }
        while (!$entity->checkAttributeUniqueValue($attribute, $object)) {
            throw new NoSuchEntityException(__('Account with telephone is already exist.'));
        }
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     * @throws NoSuchEntityException
     */
    public function beforeSave($object)
    {
        if ($this->_configProvider->isTelephoneLoginEnable() && $object->getData($this->getAttribute()->getAttributeCode())) {
            $this->checkUniqueTelephone($object);
        }
        return parent::beforeSave($object);
    }
}
