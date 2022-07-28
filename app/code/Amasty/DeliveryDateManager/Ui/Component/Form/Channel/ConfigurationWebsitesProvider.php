<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Channel;

class ConfigurationWebsitesProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    private $systemStore;

    public function __construct(\Magento\Store\Model\System\Store $systemStore)
    {
        $this->systemStore = $systemStore;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->systemStore->getStoreValuesForForm();
    }
}
