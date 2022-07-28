<?php

namespace Magenest\GoogleTagManager\Model\System\Config\Backend;

class Checkoutsteps extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magenest\GoogleTagManager\Helper\Checkoutsteps
     */
    private $checkoutSteps;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magenest\GoogleTagManager\Helper\Checkoutsteps $checkoutSteps
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magenest\GoogleTagManager\Helper\Checkoutsteps $checkoutSteps,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);

        $this->checkoutSteps = $checkoutSteps;
    }

    protected function _afterLoad()
    {
        $value = $this->getValue();
        $value = $this->checkoutSteps->makeArrayFieldValue($value);
        $this->setValue($value);

        return $this;
    }

    public function beforeSave()
    {
        $value = $this->getValue();
        $value = $this->checkoutSteps->makeStorableArrayFieldValue($value);
        $this->setValue($value);

        return $this;
    }
}
