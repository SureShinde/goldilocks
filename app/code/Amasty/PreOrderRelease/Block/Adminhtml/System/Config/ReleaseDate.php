<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Block\Adminhtml\System\Config;

use Amasty\PreOrderRelease\Model\ConfigProvider;
use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Config\Model\Config\ScopeDefiner;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class ReleaseDate extends Fieldset
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ScopeDefiner
     */
    private $scopeDefiner;

    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        ConfigProvider $configProvider,
        ScopeDefiner $scopeDefiner,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->configProvider = $configProvider;
        $this->scopeDefiner = $scopeDefiner;
    }

    public function render(AbstractElement $element)
    {
        if ($this->scopeDefiner->getScope() === ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            || $this->configProvider->isReleaseDateEnabled()
        ) {
            $result = parent::render($element);
        } else {
            $result = '';
        }

        return $result;
    }
}
