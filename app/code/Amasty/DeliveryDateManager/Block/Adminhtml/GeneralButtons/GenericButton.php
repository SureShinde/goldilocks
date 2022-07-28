<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Adminhtml\GeneralButtons;

use Magento\Backend\Block\Widget\Context;

class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->context->getRequest()->getParam('id');
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return (int)$this->context->getRequest()->getParam('order_id');
    }

    /**
     * @return string
     */
    public function getHandleName(): string
    {
        return (string)$this->context->getRequest()->getParam('handle');
    }

    /**
     * @return string
     */
    public function getModalSpace(): string
    {
        switch ($this->context->getRequest()->getParam('namespace')) {
            case "amdelivery_channel_interval_form":
                return 'intervalSet';
            case "amdelivery_channel_limit_form":
                return 'limit';
            case "amdelivery_channel_schedule_form":
                return 'schedule';
            case "amdelivery_channel_config_form":
                return 'configuration';
            default:
                return '';
        }
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
