<?php

namespace Magenest\LastName\Block\Widget;

class Name extends \Magento\Customer\Block\Widget\Name
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        // override default template location
        $this->setTemplate('Magenest_LastName::widget/name.phtml');
    }
}
