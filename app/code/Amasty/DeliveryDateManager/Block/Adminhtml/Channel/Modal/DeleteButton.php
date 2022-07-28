<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Adminhtml\Channel\Modal;

use Amasty\DeliveryDateManager\Block\Adminhtml\GeneralButtons\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        $handleName = $this->getHandleName();

        return [
            'label' => __('Delete'),
            'on_click' => '',
            'data_attribute' => [
                'mage-init' => [
                    'Amasty_DeliveryDateManager/js/view/modal/form/delete-button' => [
                        'actions' => [
                            [
                                'targetName' => 'index = ' . $handleName,
                                'actionName' => 'abstractDelete',
                                'params' => [
                                    $this->getDeleteUrl(),
                                ],
                            ]
                        ],
                        'formProvider' => $handleName . '.' . $handleName . '_data_source',
                        'idField' => $this->getIdFieldName()
                    ],
                ],
            ],
            'sort_order' => 20
        ];
    }

    /**
     * @return string
     */
    private function getDeleteUrl(): string
    {
        return $this->getUrl(
            'amasty_deliverydate/channel_' . $this->getModalSpace() . '/delete'
        );
    }

    /**
     * @return string
     */
    private function getIdFieldName(): string
    {
        switch ($this->context->getRequest()->getParam('namespace')) {
            case 'amdelivery_channel_interval_form':
                return 'set_id';
            case 'amdelivery_channel_limit_form':
                return 'limit_id';
            case 'amdelivery_channel_schedule_form':
                return 'schedule_id';
            case 'amdelivery_channel_config_form':
                return 'id';
            default:
                return '';
        }
    }
}
