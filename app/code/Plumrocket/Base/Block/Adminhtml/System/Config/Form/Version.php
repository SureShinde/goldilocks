<?php
/**
 * @package     Plumrocket_Base
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license/  End-user License Agreement
 */

namespace Plumrocket\Base\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Plumrocket\Base\Api\GetExtensionInformationInterface;
use Plumrocket\Base\Api\GetModuleVersionInterface;
use Plumrocket\Base\Model\Extension\Updates\GetLastVersionMessage;
use Plumrocket\Base\Model\IsModuleInMarketplace;

/**
 * @since 1.0.0
 */
class Version extends Field
{

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cacheManager;

    /**
     * @deprecated since 2.7.0
     * @var string
     */
    protected $wikiLink;

    /**
     * @deprecated since 2.7.0
     * @var string
     */
    protected $moduleTitle;

    /**
     * @var \Plumrocket\Base\Api\GetModuleVersionInterface
     */
    private $getModuleVersion;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $phpSerializer;

    /**
     * @var \Plumrocket\Base\Api\GetExtensionInformationInterface
     */
    private $getExtensionInformation;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    private $element;

    /**
     * @var \Plumrocket\Base\Model\IsModuleInMarketplace
     */
    private $isModuleInMarketplace;

    /**
     * @var \Plumrocket\Base\Model\Extension\Updates\GetLastVersionMessage
     */
    private $getLastVersionMessage;

    /**
     * @param \Magento\Backend\Block\Template\Context                        $context
     * @param \Magento\Framework\App\CacheInterface                          $cacheManager
     * @param \Plumrocket\Base\Api\GetModuleVersionInterface                 $getModuleVersion
     * @param \Magento\Framework\Serialize\SerializerInterface               $phpSerializer
     * @param \Plumrocket\Base\Api\GetExtensionInformationInterface          $getExtensionInformation
     * @param \Plumrocket\Base\Model\IsModuleInMarketplace                   $isModuleInMarketplace
     * @param \Plumrocket\Base\Model\Extension\Updates\GetLastVersionMessage $getLastVersionMessage
     * @param array                                                          $data
     */
    public function __construct(
        Context $context,
        CacheInterface $cacheManager,
        GetModuleVersionInterface $getModuleVersion,
        SerializerInterface $phpSerializer,
        GetExtensionInformationInterface $getExtensionInformation,
        IsModuleInMarketplace $isModuleInMarketplace,
        GetLastVersionMessage $getLastVersionMessage,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cacheManager = $cacheManager;
        $this->getModuleVersion = $getModuleVersion;
        $this->phpSerializer = $phpSerializer;
        $this->getExtensionInformation = $getExtensionInformation;
        $this->isModuleInMarketplace = $isModuleInMarketplace;
        $this->getLastVersionMessage = $getLastVersionMessage;
    }

    /**
     * Render version field considering request parameter
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->element = $element;
        return $this->getModuleInfoHtml();
    }

    /**
     * Receive url to extension documentation
     *
     * @return string
     */
    public function getWikiLink(): string
    {
        return $this->wikiLink ?: $this->getExtensionInformation->execute(
            $this->getModuleName()
        )->getDocumentationLink();
    }

    /**
     * Receive extension name
     *
     * @return string
     */
    public function getModuleTitle(): string
    {
        return $this->moduleTitle ?: $this->getExtensionInformation->execute($this->getModuleName())->getTitle();
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        if ($this->element && $this->element->getData('field_config/pr_extension_name')) {
            return $this->element->getData('field_config/pr_extension_name');
        }

        return parent::getModuleName();
    }

    /**
     * Receive extension information html
     *
     * @return string
     */
    public function getModuleInfoHtml()
    {
        $moduleVersion = $this->getModuleVersion->execute($this->getModuleName());

        if ($this->isModuleInMarketplace->execute($this->getModuleName())) {
            $message = $this->getModuleTitle() . ' v' . $moduleVersion . ' was developed by Plumrocket Inc. ' .
                'If you have any questions, please contact us at ' .
                '<a href="mailto:support@plumrocket.com">support@plumrocket.com</a>.';
        } else {
            $message = $this->getModuleTitle() . ' v' . $moduleVersion . ' was developed by ' .
                '<a href="https://plumrocket.com" target="_blank">Plumrocket Inc</a>.
            For manual & video tutorials please refer to ' .
                '<a href="' . $this->getWikiLink() . '" target="_blank">our online documentation</a>.';
        }

        $html = '<tr><td class="label" colspan="4" style="text-align: left;">' .
            '<div style="padding:10px;background-color:#f8f8f8;border:1px solid #ddd;margin-bottom:7px;">
            ' . $message . '</div></td></tr>';

        $mvd = strtolower($this->getModuleName()) . '_last_module_version';
        $tags = [];

        if ($mcache = $this->cacheManager->load($mvd)) {
            $mData = $this->phpSerializer->unserialize($mcache);
            $message = $mData['message'];
            $version = $mData['newv'];
        } else {
            $mcache = $this->getLastVersionMessage->execute($this->getModuleName());
            $message = $mcache['message'];
            $version = $mcache['newv'];
            if (!empty($message) && !empty($version)) {
                $this->cacheManager->save($this->phpSerializer->serialize($mcache), $mvd, $tags, 86400);
            }
        }

        $messageHtml = '';

        if (!empty($message) && !empty($version)) {
            if (version_compare($version, $moduleVersion, '>')) {
                $messageHtml = "<script type='text/javascript'>
                     require(['jquery'], function ($) {
                         var messageBlock = $('.page-main-actions'),
                         messageText = '" . $message . "';
                         if (messageBlock) {
                             messageBlock.after('<div id=\'plumbaseMessageBlock\' class=\'message message-notice notice\'><div data-ui-id=\'messages-message-notice\'>'
                                 + messageText
                                 + '</div></div><br/>'
                             );
                         }
                     });
                </script>";
            }
        }

        return $html . $messageHtml;
    }
}
