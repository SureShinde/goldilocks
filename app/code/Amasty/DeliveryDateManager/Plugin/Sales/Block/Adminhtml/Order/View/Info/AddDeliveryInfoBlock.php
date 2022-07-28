<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Plugin\Sales\Block\Adminhtml\Order\View\Info;

use Amasty\DeliveryDateManager\Block\Adminhtml\Sales\Order\View\Deliverydate;
use Magento\Sales\Block\Adminhtml\Order\View\Info;

/**
 * Invoice doesn't have output containers for extensions.
 * Info Block outputs in all Sales view.
 */
class AddDeliveryInfoBlock
{
    /**
     * @see Info
     *
     * @param Info $subject
     * @param string $html
     *
     * @return string
     */
    public function afterToHtml(
        Info $subject,
        string $html
    ): string {
        /** @var Deliverydate $insertBlock */
        $insertBlock = $subject->getChildBlock('amasty_delivery_date');
        if ($insertBlock) {
            $insertBlock->setTemplate('Amasty_DeliveryDateManager::delivery_view.phtml');
            $html .= $insertBlock->toHtml();
        }

        return $html;
    }
}
