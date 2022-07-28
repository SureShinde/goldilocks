<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Adminhtml\GeneralButtons;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'on_click' => '',
            'sort_order' => 50
        ];
    }
}
