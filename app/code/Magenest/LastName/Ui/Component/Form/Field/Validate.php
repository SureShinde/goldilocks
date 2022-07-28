<?php

namespace Magenest\LastName\Ui\Component\Form\Field;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;

class Validate extends \Magento\Ui\Component\Form\Field
{
    /**
     * @var \Magenest\LastName\Helper\Data
     */
    protected $_helperLastName;

    /**
     * Constructor
     * @param \Magenest\LastName\Helper\Data $helperLastName
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        \Magenest\LastName\Helper\Data $helperLastName,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_helperLastName = $helperLastName;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare component configuration
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepare()
    {
        parent::prepare();

        $config = $this->getData('config');
        $config["validation"]["required-entry"] = false;
        $config['visible'] = false;
        if ($this->_helperLastName->isLastnameRequired()) {
            $config['visible'] = true;
            $config["validation"]["required-entry"] = true;
        }
        $this->setData('config', $config);
    }
}
