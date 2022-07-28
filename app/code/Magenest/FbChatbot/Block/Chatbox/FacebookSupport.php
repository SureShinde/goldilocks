<?php
/**
 * Copyright (c) Magenest, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\FbChatbot\Block\Chatbox;

use Magenest\FbChatbot\Model\Config\Source\Type;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class FacebookSupport
 * @package Magenest\FacebookSupportLive\Block\Chatbox
 */
class FacebookSupport extends Template
{
    protected $serializer;

    /**
     * FacebookSupport constructor.
     * @param Template\Context $context
     * @param Json $serializer
     * @param array $data
     */
    public function __construct(Template\Context $context, Json $serializer, array $data = [])
    {
        parent::__construct($context, $data);
        $this->serializer = $serializer;
    }
    /**
     * @return mixed
     */
    public function getLinkPage()
    {
        return $this->_scopeConfig->getValue('fb_chatbot/fb_live_chat/link_page');
    }

    /**
     * @return string
     */
    public function getPageId()
    {
        return $this->_scopeConfig->getValue('fb_chatbot/fb_live_chat/page_id');
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->_scopeConfig->getValue('fb_chatbot/fb_live_chat/app_id_page');
    }

    /**
     * @return bool
     */
    public function isEnabledChatBox()
    {
        return $this->_scopeConfig->isSetFlag('fb_chatbot/fb_live_chat/enable_fb');
    }

    /**
     * @return bool
     */
    public function isUseCode()
    {
        return $this->getConfigType() == Type::USE_CODE;
    }

    /**
     * @return bool
     */
    public function isUseSettings()
    {
        return $this->getConfigType() == Type::USE_SETTINGS;
    }

    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        $store = $this->_storeManager->getStore();
        $settings = $this->_scopeConfig->getValue('fb_chatbot/fb_live_chat/settings', 'stores', $store->getId());

        return $settings ? $this->serializer->unserialize($settings) : null;
    }

    /**
     * @return string
     */
    public function getSettingsHtml()
    {
        $settings = $this->getSettings();
        $html = ' ';
        if (!$settings) {
            return $html;
        }
        foreach ((array)$settings as $key => $setting) {
            $html .= $setting['name'] . '="' . $setting['value'] . '" ';
        }
        return $html;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->_scopeConfig->getValue('fb_chatbot/fb_live_chat/code');
    }

    /**
     * @return int
     */
    public function getConfigType()
    {
        return $this->_scopeConfig->getValue('fb_chatbot/fb_live_chat/config_type');
    }
}
