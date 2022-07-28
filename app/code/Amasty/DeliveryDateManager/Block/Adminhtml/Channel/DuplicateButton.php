<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Adminhtml\Channel;

use Amasty\DeliveryDateManager\Block\Adminhtml\GeneralButtons\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DuplicateButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        $data = [];
        if ($this->getId()) {
            $data = [
                'label' => __('Duplicate'),
                'class' => 'duplicate',
                'id' => 'channel-edit-duplicate-button',
                'data_attribute' => [
                    'url' => $this->getDuplicateUrl()
                ],
                'on_click' => 'setLocation(\'' . $this->getDuplicateUrl() . '\')',
                'sort_order' => 30,
                'aclResource' => 'Amasty_DeliveryDateManager::deliverydate_channel'
            ];
        }

        return $data;
    }

    /**
     * @return string
     */
    private function getDuplicateUrl(): string
    {
        return $this->getUrl('amasty_deliverydate/channel/duplicate', ['id' => $this->getId()]);
    }
}
