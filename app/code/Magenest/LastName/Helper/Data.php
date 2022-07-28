<?php

namespace Magenest\LastName\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_PATH_LASTNAME_REQUIRED = 'customer/address/lastname_required';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isLastnameRequired()
    {
        return (bool) $this->scopeConfig->getValue(
            self::CONFIG_PATH_LASTNAME_REQUIRED,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $this->getWebsiteId()
        );
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWebsiteId()
    {
        if ($websiteId = $this->_getRequest()->getParam('website')) {
            return $websiteId;
        }
        return $this->_storeManager->getWebsite()->getId();
    }
}
