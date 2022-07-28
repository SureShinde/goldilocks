<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Adminhtml\Sales\Order\Edit;

use Amasty\DeliveryDateManager\Block\Adminhtml\GeneralButtons\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }
    /**
     * @return string
     */
    private function getBackUrl(): string
    {
        return $this->getUrl(
            'sales/order/view',
            ['order_id' => $this->getOrderId()]
        );
    }
}
