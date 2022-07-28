<?php

namespace Magenest\GoogleTagManager\Block\Adminhtml\Form\Field\Product\Attributes;

class Code extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Data\Collection
     */
    private $collection;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Set collection object
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return void
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * Get collection object
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Retrieve list of product attributes for select
     *
     * @return array
     */
    public function getAttributes()
    {
        $result = [];
        foreach ($this->getCollection() as $item) {
            $result[$item->getAttributeCode()] = $item->getDefaultFrontendLabel();
        }

        return $result;
    }

    /**
     * Set name of an input
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->prepareCollection();

            foreach ($this->getAttributes() as $id => $label) {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
                $this->addOption($id, \addslashes($label));
            }
        }

        return parent::_toHtml();
    }

    /**
     * Prepare product attributes grid collection object
     *
     * @return $this
     */
    private function prepareCollection()
    {
        $this->setCollection($this->collectionFactory->create()->addVisibleFilter());

        return $this;
    }
}
