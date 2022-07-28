<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Plugin\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form;

use Amasty\DeliveryDateManager\Block\Adminhtml\Sales\Order\Create\Deliverydate as DeliverydateCreate;
use Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form;

/**
 * Insert Delivery Information Block for Create Order
 */
class AddDeliveryFieldsBlock
{
    /**
     * @see Form
     *
     * @param Form $subject
     * @param string $html
     * @return string
     */
    public function afterToHtml(
        Form $subject,
        string $html
    ): string {
        $insertBlock = $subject->getLayout()->createBlock(DeliverydateCreate::class);
        $html .= $insertBlock->toHtml();

        return $html;
    }
}
