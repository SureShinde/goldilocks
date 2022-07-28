<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Guest;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;

class Save extends \Amasty\DeliveryDateManager\Controller\Deliverydate\Save implements HttpPostActionInterface
{
    /**
     * @param Redirect $result
     * @param int $orderId
     *
     * @return void
     */
    protected function setRedirectUrl(Redirect $result, int $orderId): void
    {
        $result->setPath('sales/guest/view');
    }
}
