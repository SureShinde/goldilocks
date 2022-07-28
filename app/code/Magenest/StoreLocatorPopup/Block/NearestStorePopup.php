<?php

namespace Magenest\StoreLocatorPopup\Block;

use Amasty\Storelocator\Model\ConfigProvider;
use Magento\Framework\View\Element\Template;

class NearestStorePopup extends Template
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @param Template\Context $context
     * @param ConfigProvider $configProvider
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ConfigProvider   $configProvider,
        array            $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
    }

    /**
     * @return string
     */
    public function getBaseUrlStore(): string
    {
        $locatorPage = $this->configProvider->getUrl();
        return $this->getUrl($locatorPage);
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->configProvider->getApiKey() ?: '';
    }
}
