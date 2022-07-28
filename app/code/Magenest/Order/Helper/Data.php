<?php

namespace Magenest\Order\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ORDER_SOURCE_FIELD = 'magenest_order/general/source_fields';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @param $storeId
     * @return array
     */
    public function getSourceField($storeId = null): array
    {
        $sourceConfig = $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_SOURCE_FIELD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($sourceConfig) {
            $sourceConfig = json_decode($sourceConfig, true);
            $data = [];
            foreach ($sourceConfig as $sourceFields) {
                $data[$sourceFields['source_fields']] = $sourceFields['source_fields'];
            }
            return $data;
        }
        return [];
    }
}
