<?php
namespace Magenest\FbChatbot\Api\Data;

interface MessageInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    const ID = 'message_id';
    const ACTIVE = 'is_active';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const SENT_TIMES = 'sent_times';
    const MESSAGE_TYPES = 'message_types';
    const TITLE = 'title';
    const TEXT = 'text';
    const SUBTITLE = 'subtitle';
    const ACTION = 'is_action';
    const IMAGE_DETAIL = 'image_detail';
    const MESSENGER_EXTENSIONS = 'messenger_extensions';
    const WEBVIEW_HEIGHT = 'webview_height_ratio';
    const MEDIA_TYPE = 'media_type';
    const CATEGORY_LEVEL = 'category_level';
    const TYPING = 'typing';
    const TYPING_TIME = 'typing_time';
    const MESSAGE_CODE  = 'code';

    public function getId();

    public function setId($id);

    public function getActive();

    public function setActive($active);

    public function getName();

    public function setName($name);

    public function getDescription();

    public function setDescription($description);

    public function getSentTimes();

    public function setSentTime($sentTimes);

    public function getMessageType();

    public function setMessageType($messageType);

    public function getTitle();

    public function setTitle($title);

    public function getSubtitle();

    public function setSubtitle($title);

    public function getImage();

    public function setImage($image);

    public function getAction();

    public function setAction($action);

    public function getImageDetail();

    public function setImageDetail($imageDetail);

    public function getMessengerExtension();

    public function setMessengerExtension($extension);

    public function getWebviewHeight();

    public function setWebviewHeight($viewHeight);

    public function getMediaType();

    public function setMediaType($mediaType);

    public function getCategoryLevel();

    public function setCategoryLevel($level);

    public function getTyping();

    public function setTyping($typing);

    public function getTypingTime();

    public function setTypingTime($time);

    public function getCode();

    public function setCode($code);

}
