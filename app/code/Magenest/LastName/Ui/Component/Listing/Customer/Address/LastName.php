<?php
namespace Magenest\LastName\Ui\Component\Listing\Customer\Address;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magenest\LastName\Helper\Data as LastNameHelper;

class LastName extends \Magento\Ui\Component\Listing\Columns\Column {

    protected $_helperLastName;

    /**
     * LastName constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param LastNameHelper $helperLastName
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        LastNameHelper $helperLastName,
        array $components = [],
        array $data = []
    ){
        $this->_helperLastName = $helperLastName;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepare()
    {
        if(!$this->_helperLastName->isLastnameRequired()) {
            $this->setData('config', array_merge(['componentDisabled' => true], (array)$this->getData('config')));
        }
        parent::prepare();
    }
}