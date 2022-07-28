<?php

namespace Acommerce\Gtm\Plugin;

class GuestPaymentInformation
{
    /**
     * @var \Acommerce\Gtm\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    
    /**
     * @param \Acommerce\Gtm\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Acommerce\Gtm\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository)
    {
        $this->helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param \Magento\Checkout\Model\GuestPaymentInformationManagement $subject
     * @return int Order ID.
     */
    public function afterSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\GuestPaymentInformationManagement $subject,
        $result
        )
    {
        if (!$this->helper->isEnabled()) {
            return $result;
        }

        $orderId = $result;

        $order = $this->orderRepository->get($orderId);
        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        $paymentMethodTitle = $additionalInformation['method_title'];

        $this->_checkoutSession->setCheckoutOptionsData($this->helper->addCheckoutStepPushData('2', $paymentMethodTitle));

        return $result;
    }


}
