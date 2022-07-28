<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\LastName\Plugin\Order\Create\Form;

use Magento\Framework\Data\Form;

/**
 * Class Address
 * @package Magenest\LastName\Plugin\Order\Create\Form
 */
class Address
{

    protected $_helperLastName;

    /**
     * Address constructor.
     * @param \Magenest\LastName\Helper\Data $helperLastName
     */
    public function __construct(\Magenest\LastName\Helper\Data $helperLastName)
    {
        $this->_helperLastName = $helperLastName;
    }

    /**
     * After get Form
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Form\Address $subject
     * @param Form $form
     * @return Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetForm(
        \Magento\Sales\Block\Adminhtml\Order\Create\Form\Address $subject,
        Form $form
    ) {
        if(!$this->_helperLastName->isLastnameRequired()) {
            $form->getElement('main')
                ->removeField('lastname');
        }

        return $form;
    }
}
