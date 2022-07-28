<?php

namespace Magenest\FbChatbot\Model;

use Magenest\FbChatbot\Api\Data\ButtonInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Button extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, ButtonInterface
{
    /**
     * Button types
     */
    const BUTTON_TYPE_TEXT = 1;
    const BUTTON_TYPE_URL = 2;
    const BUTTON_TYPE_TELEPHONE = 3;
    const BUTTON_TYPE_ACTION = 4;

    const BUTTON_ACTION_PRODUCT = 1;
    const BUTTON_ACTION_CATEGORY = 2;
    const BUTTON_ACTION_GENERAL = 3;

    const SHOW_PRODUCTS_CODE = 'show_products';
    const VIEW_PRODUCT_DETAIL_CODE = 'view_product_detail';
    const VIEW_CATEGORY_DETAIL_CODE = 'view_category_detail';
    const ADD_TO_CART_CODE = 'add_to_cart';
    const MODIFY_CART_AND_CHECKOUT_CODE = 'modify_cart_and_checkout';
    const WRITE_PRODUCT_REVIEW_CODE = 'write_product_review';
    const VIEW_ORDER_DETAIL_CODE = 'view_order_detail';

    const BUTTON_URL = 'web_url';
    const BUTTON_PHONE_NUMBER = 'phone_number';
    const BUTTON_POSTBACK = 'postback';

    const CACHE_TAG = 'magenest_fbchatbot_button';

    protected $_cacheTag = 'magenest_fbchatbot_button';

    protected $_eventPrefix = 'magenest_fbchatbot_button';

    /**
     * Button types cache for lazy getter
     *
     * @var array
     */
    protected $_buttonTypes;

    /**
     * @var array|null
     */
    protected $_buttonMessageTypes;

    protected function _construct()
    {
        $this->_init(\Magenest\FbChatbot\Model\ResourceModel\Button::class);
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

    /**
     * Retrieve button types
     *
     * @return array
     */
    public function getButtonTypes()
    {
        if ($this->_buttonTypes === null) {
            $this->_buttonTypes = [
                Button::BUTTON_TYPE_TEXT => __('Show next message'),
                Button::BUTTON_TYPE_URL => __('Show URL'),
                Button::BUTTON_TYPE_TELEPHONE => __('Phone number'),
                Button::BUTTON_TYPE_ACTION => __('Take action'),
            ];
        }
        return $this->_buttonTypes;
    }

    public function getButtonMessageTypes(){
        if ($this->_buttonMessageTypes === null) {
            $this->_buttonMessageTypes = [
                Button::BUTTON_ACTION_PRODUCT => __('Product'),
                Button::BUTTON_ACTION_CATEGORY => __('Category'),
                Button::BUTTON_ACTION_GENERAL => __('General')
            ];
        }
        return $this->_buttonMessageTypes;
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

    public function getCode()
    {
        return (string) $this->getData(self::CODE);
    }

    public function setCode($code)
    {
        $this->setData(self::CODE,$code);
        return $this;
    }

    public function getDescription()
    {
        return (string) $this->getData(self::DESCRIPTION);
    }

    public function setDescription($description)
    {
        $this->setData(self::DESCRIPTION,$description);
        return $this;
    }

    public function getButtonType()
    {
        return (string) $this->getData(self::BUTTON_TYPE);
    }

    public function setButtonType($buttonType)
    {
        $this->setData(self::BUTTON_TYPE,$buttonType);
        return $this;
    }

    public function getTitle()
    {
        return (string) $this->getData(self::TITLE);
    }

    public function setTitle($title)
    {
        $this->setData(self::TITLE,$title);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCustomAttribute($attributeCode)
    {
        // TODO: Implement getCustomAttribute() method.
    }

    /**
     * @inheritDoc
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        // TODO: Implement setCustomAttribute() method.
    }

    /**
     * @inheritDoc
     */
    public function getCustomAttributes()
    {
        // TODO: Implement getCustomAttributes() method.
    }

    /**
     * @inheritDoc
     */
    public function setCustomAttributes(array $attributes)
    {
        // TODO: Implement setCustomAttributes() method.
    }
}
