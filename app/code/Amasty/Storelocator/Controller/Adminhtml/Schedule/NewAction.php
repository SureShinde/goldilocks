<?php

namespace Amasty\Storelocator\Controller\Adminhtml\Schedule;

/**
 * Class NewAction
 */
class NewAction extends \Amasty\Storelocator\Controller\Adminhtml\Schedule
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
