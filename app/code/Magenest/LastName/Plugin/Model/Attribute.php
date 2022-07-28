<?php

namespace Magenest\LastName\Plugin\Model;

class Attribute
{
    /**
     * @var \Magenest\LastName\Helper\Data
     */
    protected $_helperLastName;

    /**
     * Attribute constructor.
     * @param \Magenest\LastName\Helper\Data $helperLastName
     */
    public function __construct(
        \Magenest\LastName\Helper\Data $helperLastName
    ) {
        $this->_helperLastName = $helperLastName;
    }

    /**
     * @param \Magento\Customer\Model\Attribute $subject
     * @param $result
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetIsRequired(\Magento\Customer\Model\Attribute $subject, $result)
    {
        if ($subject->getAttributeCode() == 'lastname') {
            // if only First Name is required
            if (!$this->_helperLastName->isLastnameRequired()) {
                return 0;
            }
        }
        return $result;
    }
}
