<?php
namespace Magenest\PendingCod\Plugin\Block\Adminhtml\Order;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class View
 * @package Magenest\PendingCod\Plugin\Block\Adminhtml\Order
 */
class View extends \Magento\Customer\Block\Adminhtml\Edit\GenericButton
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * View constructor.
     * @param Context $context
     * @param Registry $registry
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        Registry $registry,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__Construct($context, $registry);
        $this->orderRepository = $orderRepository;
        $this->authorization = $context->getAuthorization();
    }

    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View $view
     */
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view)
    {
        $orderId = $view->getOrderId();
        $isAllowed = $this->authorization->isAllowed('Magenest_PendingCod::approve');
        $order = $this->orderRepository->get($orderId);
        if ($order->getStatus() == 'pending_cod_approval' && $isAllowed) {
            $message ='Are you sure to approve this order?';
            $url = $view->getUrl('pendingcod/approveorder/', ['order_id' => $orderId]);
            $view->addButton(
                'approve_pending_cod',
                [
                    'label' => __('Approve Order'),
                    'class' => 'approve_pending_cod',
                    'onclick' => "confirmSetLocation('{$message}', '{$url}')"
                ]
            );
        }
    }
}
