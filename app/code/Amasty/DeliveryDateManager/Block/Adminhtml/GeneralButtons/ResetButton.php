<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Adminhtml\GeneralButtons;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\GenericButton;

class ResetButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Reset'),
            'class' => 'reset',
            'on_click' => 'location.reload();',
            'sort_order' => 30
        ];
    }
}
