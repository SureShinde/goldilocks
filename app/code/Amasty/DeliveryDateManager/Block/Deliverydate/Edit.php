<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Deliverydate;

use Magento\Customer\Model\Session;

/**
 * Delivery Date Edit
 */
class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * @var LayoutProcessorInterface[]
     */
    private $layoutProcessors;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Session $session,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->session = $session;
        $this->layoutProcessors = $layoutProcessors;
    }

    /**
     * @return string
     */
    public function getJsLayout(): string
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }

        return parent::getJsLayout();
    }

    /**
     * Get order id from request
     * @return int
     */
    public function getOrderId(): int
    {
        return (int)$this->getRequest()->getParam('order_id');
    }

    /**
     * Return url for form
     *
     * @return string
     */
    public function getSaveUrl(): string
    {
        if ($this->isLoggedIn()) {
            return $this->getUrl('deliverydate/deliverydate/save', ['order_id' => $this->getOrderId()]);
        }
        return $this->getUrl('deliverydate/guest/save', ['order_id' => $this->getOrderId()]);
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl(): string
    {
        if ($this->isLoggedIn()) {
            return $this->getUrl('sales/order/view', ['order_id' => $this->getOrderId()]);
        }
        return $this->getUrl('sales/guest/view');
    }

    /**
     * @return bool
     */
    private function isLoggedIn(): bool
    {
        return (bool)$this->session->isLoggedIn();
    }
}
