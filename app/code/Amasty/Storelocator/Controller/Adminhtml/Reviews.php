<?php

namespace Amasty\Storelocator\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Class Reviews
 */
abstract class Reviews extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_Storelocator::reviews';
}
