<?php
namespace Magenest\FbChatbot\Model;

use Magenest\FbChatbot\Api\Data\MessageInterface;
use Magenest\FbChatbot\Setup\Patch\Data\InsertNewMessageData;
use Magento\Framework\DataObject\IdentityInterface;

class Message extends \Magento\Framework\Model\AbstractModel implements IdentityInterface,MessageInterface
{
    const MESSAGE_CANNOT_DELETE = [
        self::DEFAULT_MESSAGE_CODE,
        self::GET_STARTED_CODE,
        self::SHOW_CATEGORY_CODE,
        self::CONTINUE_WITH_BOT_CODE,
        self::REQUEST_HUMAN_SUPPORT_CODE,
        self::VIEW_ADDRESS_CODE,
        self::ABOUT_US_CODE,
        self::WHAT_WOULD_YOU_LIKE_TO_DO_CODE,
        self::SHOW_PRODUCT_BUTTONS_CODE,
        self::MY_ORDERS_CODE,
        self::MY_WISHLIST_CODE
    ];

    const GET_STARTED_CODE = 'get_started';
    const DEFAULT_MESSAGE_CODE = 'default_message';
    const SHOW_PRODUCT_BUTTONS_CODE = 'show_product_buttons';
    const SHOW_CATEGORY_CODE = "show_category";
    const CONTINUE_WITH_BOT_CODE = "continue_with_bot";
    const REQUEST_HUMAN_SUPPORT_CODE = "request_human_support";
    const VIEW_ADDRESS_CODE = "view_address";
    const ABOUT_US_CODE = "about_us";
    const WHAT_WOULD_YOU_LIKE_TO_DO_CODE = "what_would_you_like_to_do";
    const MY_ORDERS_CODE = "my_orders";
    const MY_WISHLIST_CODE = "my_wishlist";

    const GET_STARTED_MESSAGE = 'Get Started';

    const DEFAULT_MESSAGE = 'Default Message';

    const REQUEST_HUMAN_SUPPORT = 'Request Human Support';
    const SHOW_MY_ORDERS =  'My orders';
    const CONTINUE_WITH_BOT = 'Continue with Bot';
    const SHOW_WISHLIST = 'My Wishlist';
    const PRODUCT_BUTTONS_FROM_MESSAGE_ID = 9;
    const ORDER_BUTTONS_FROM_MESSAGE_ID = 10;

    const MESSAGE_TYPE_PRODUCT = 1;
    const MESSAGE_TYPE_CATEGORIES = 2;
    const MESSAGE_TYPE_TEXT = 3;
    const MESSAGE_TYPE_BUTTON_AND_TEXT = 4;
    const MESSAGE_TYPE_QUICK_REPLY = 5;
    const MESSAGE_TYPE_ORDER = 6;
    const MESSAGE_TYPE_TEXT_IMAGE = 8;
    const MESSAGE_TYPE_WISHLIST = 7;
    const MESSAGE_TYPE_CREATE_ORDER = 9;
    const MESSAGE_TYPE_CANCEL_ORDER_CREATION = 10;

    const MESSAGE_STATUS_ACTIVE = 1;
    const MESSAGE_STATUS_INACTIVE = 0;

    const MESSAGE_TYPING_ACTIVE = 1;
    const MESSAGE_TYPING_INACTIVE = 0;

    const MESSAGE_EXTENSIONS_TRUE = 1;
    const MESSAGE_EXTENSIONS_FALSE = 0;

    const MESSAGE_WEBVIEW_HEIGHT_COMPACT = 1;
    const MESSAGE_WEBVIEW_HEIGHT_TALL = 2;
    const MESSAGE_WEBVIEW_HEIGHT_FULL = 3;

    const MESSAGE_MEDIA_IMAGE = 1;
    const MESSAGE_MEDIA_VIDEO = 2;

    const CACHE_TAG = 'magenest_fbchatbot_message';

    protected $_cacheTag = 'magenest_fbchatbot_message';

    protected $_eventPrefix = 'magenest_fbchatbot_message';

    /**
     * @var array|null
     */
    protected $_messageTypes;

    /**
     * @var array|null
     */
    protected $_messageStatus;

    /**
     * @var array|null
     */
    protected $_messageExtensions;

    /**
     * @var array|null
     */
    protected $_webviewHeight;

    protected function _construct()
    {
        $this->_init(\Magenest\FbChatbot\Model\ResourceModel\Message::class);
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
     * Retrieve message types
     *
     * @return array
     */
    public function getMessageTypes()
    {
        if ($this->_messageTypes === null) {
            $this->_messageTypes = [
                \Magenest\FbChatbot\Model\Message::MESSAGE_TYPE_PRODUCT => __('Product Display'),
                \Magenest\FbChatbot\Model\Message::MESSAGE_TYPE_CATEGORIES => __('Category Display'),
                \Magenest\FbChatbot\Model\Message::MESSAGE_TYPE_TEXT => __('Text'),
                \Magenest\FbChatbot\Model\Message::MESSAGE_TYPE_TEXT_IMAGE => __('Text & Image'),
                \Magenest\FbChatbot\Model\Message::MESSAGE_TYPE_ORDER => __('View Order'),
                \Magenest\FbChatbot\Model\Message::MESSAGE_TYPE_WISHLIST => __('View Wishlist'),
                \Magenest\FbChatbot\Model\Message::MESSAGE_TYPE_CREATE_ORDER => __('Create Order'),
                \Magenest\FbChatbot\Model\Message::MESSAGE_TYPE_CANCEL_ORDER_CREATION => __('Cancel Order Creation'),
            ];
        }
        return $this->_messageTypes;
    }

    public function getMessageStatus()
    {
        if ($this->_messageStatus === null) {
            $this->_messageStatus = [
                \Magenest\FbChatbot\Model\Message::MESSAGE_STATUS_ACTIVE => __('Active'),
                \Magenest\FbChatbot\Model\Message::MESSAGE_STATUS_INACTIVE => __('Inactive'),
            ];
        }
        return $this->_messageStatus;
    }

    public function getMessageExtensions()
    {
        if ($this->_messageExtensions === null) {
            $this->_messageExtensions = [
                \Magenest\FbChatbot\Model\Message::MESSAGE_EXTENSIONS_TRUE => __('True'),
                \Magenest\FbChatbot\Model\Message::MESSAGE_EXTENSIONS_FALSE => __('False'),
            ];
        }
        return $this->_messageExtensions;
    }

    public function getWebviewHeights()
    {
        if ($this->_webviewHeight === null) {
            $this->_webviewHeight = [
                \Magenest\FbChatbot\Model\Message::MESSAGE_WEBVIEW_HEIGHT_COMPACT => __('Compact'),
                \Magenest\FbChatbot\Model\Message::MESSAGE_WEBVIEW_HEIGHT_TALL => __('Tall'),
                \Magenest\FbChatbot\Model\Message::MESSAGE_WEBVIEW_HEIGHT_FULL => __('Full'),
            ];
        }
        return $this->_webviewHeight;
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
        return (string) $this->getData(self::ACTIVE);
    }

    public function setActive($active)
    {
        $this->setData(self::ACTIVE,$active);
        return $this;
    }

    public function getName()
    {
        return (string) $this->getData(self::NAME);
    }

    public function setName($name)
    {
        $this->setData(self::NAME,$name);
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

    public function getSentTimes()
    {
        return (string) $this->getData('sent_times');
    }

    public function setSentTime($sentTimes)
    {
        $this->setData('sent_times',$sentTimes);
        return $this;
    }

    public function getMessageType()
    {
        return (string) $this->getData(self::MESSAGE_TYPES);
    }

    public function setMessageType($messageType)
    {
        $this->setData(self::MESSAGE_TYPES,$messageType);
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

    public function getSubtitle()
    {
        return (string) $this->getData(self::SUBTITLE);
    }

    public function setSubtitle($subtitle)
    {
        $this->setData(self::SUBTITLE,$subtitle);
        return $this;
    }

    public function getImage()
    {
        return (string) $this->getData('image');
    }

    public function setImage($image)
    {
        $this->setData('image',$image);
        return $this;
    }

    public function getAction()
    {
        return (string) $this->getData(self::ACTION);
    }

    public function setAction($action)
    {
        $this->setData(self::ACTION,$action);
        return $this;
    }

    public function getImageDetail()
    {
        return (string) $this->getData('image_detail');
    }

    public function setImageDetail($imageDetail)
    {
        $this->setData('image_detail',$imageDetail);
        return $this;
    }

    public function getMessengerExtension()
    {
        return (string) $this->getData('messenger_extensions');
    }

    public function setMessengerExtension($extension)
    {
        $this->setData('messenger_extensions',$extension);
        return $this;
    }

    public function getWebviewHeight()
    {
        return (string) $this->getData('webview_height_ratio');
    }

    public function setWebviewHeight($viewHeight)
    {
        $this->setData('webview_height_ratio',$viewHeight);
        return $this;
    }

    public function getMediaType()
    {
        return (string) $this->getData(self::MEDIA_TYPE);
    }

    public function setMediaType($mediaType)
    {
        $this->setData(self::MEDIA_TYPE,$mediaType);
        return $this;
    }

    public function getCategoryLevel()
    {
        return (string) $this->getData(self::CATEGORY_LEVEL);
    }

    public function setCategoryLevel($level)
    {
        $this->setData(self::CATEGORY_LEVEL,$level);
        return $this;
    }


    public function getTyping()
    {
        return (string) $this->getData(self::TYPING);
    }

    public function setTyping($typing)
    {
        $this->setData(self::TYPING,$typing);
        return $this;
    }

    public function getTypingTime()
    {
        return (string) $this->getData(self::TYPING_TIME);
    }

    public function setTypingTime($time)
    {
        $this->setData(self::TYPING_TIME,$time);
        return $this;
    }

    public function getCode()
    {
        return (string) $this->getData(self::MESSAGE_CODE);
    }

    public function setCode($code)
    {
        $this->setData(self::MESSAGE_CODE, $code);
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
