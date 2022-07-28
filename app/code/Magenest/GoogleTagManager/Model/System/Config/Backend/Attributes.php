<?php

namespace Magenest\GoogleTagManager\Model\System\Config\Backend;

class Attributes extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magenest\GoogleTagManager\Helper\AttributeCodes
     */
    private $attributeCodes;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magenest\GoogleTagManager\Helper\AttributeCodes $attributeCodes
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magenest\GoogleTagManager\Helper\AttributeCodes $attributeCodes,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);

        $this->attributeCodes = $attributeCodes;
    }

    /**
     * @inheritDoc
     *
     * In addition, it converts serialised string value to array
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $value = $this->attributeCodes->makeArrayFieldValue($value);
        $this->setValue($value);

        return $this;
    }

    /**
     * @inheritDoc
     *
     * In addition, it converts array values to serialised string
     *
     * @return $this|\Magento\Framework\App\Config\Value
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $value = $this->attributeCodes->makeStorableArrayFieldValue($value);
        $this->setValue($value);

        return $this;
    }
}
