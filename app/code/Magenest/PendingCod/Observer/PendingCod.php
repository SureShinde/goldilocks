<?php
namespace Magenest\PendingCod\Observer;

use Magenest\PendingCod\Model\PendingOrderFactory;
use Magenest\PendingCod\Model\ResourceModel\PendingOrderFactory as ResourceFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class PendingCod
 * @package Magenest\PendingCod\Observer
 */
class PendingCod implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var GetSourceItemsBySkuInterface
     */
    protected $sourceItemsBySku;

    /**
     * @var SourceRepositoryInterface
     */
    protected $sourceRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var PendingOrderFactory
     */
    protected $pendingOrderFactory;

    /**
     * @var ResourceFactory
     */
    protected $pendingOrderResourceFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    public function __construct(
        CollectionFactory $statusCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        Session $customerSession,
        GetSourceItemsBySkuInterface $sourceItemsBySku,
        SourceRepositoryInterface $sourceRepository,
        ScopeConfigInterface $scopeConfig,
        PendingOrderFactory $pendingOrderFactory,
        LoggerInterface $logger,
        ResourceFactory $pendingOrderResourceFactory
    ) {
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->customerSession = $customerSession;
        $this->sourceItemsBySku = $sourceItemsBySku;
        $this->sourceRepository = $sourceRepository;
        $this->scopeConfig = $scopeConfig;
        $this->pendingOrderFactory = $pendingOrderFactory;
        $this->pendingOrderResourceFactory = $pendingOrderResourceFactory;
        $this->_logger = $logger;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $orderId = $observer->getData('order')->getData('id');
        $order = $this->orderRepository->get($orderId);
        $paymentMethod = $order->getPayment()->getMethod();
        $pendingCodStatus = 'pending_cod_approval';
        if (!$this->customerSession->isLoggedIn() && $paymentMethod == 'cashondelivery' && $order->getRemoteIp()) {
            try {
                $orderState = Order::STATE_NEW;
                $order->setState($orderState)->setStatus($pendingCodStatus);
                $this->orderRepository->save($order);
                $data = [
                    'pending_cod_order_id' => $orderId,
                    'email_sent' => 0,
                    'send_email' => 1
                ];
                $model = $this->pendingOrderFactory->create();
                $model->setData($data);
                $resourceModel = $this->pendingOrderResourceFactory->create();
                $resourceModel->save($model);
            } catch (\Exception $e){
                $this->_logger->error($e->getMessage());
                return;
            }
        }
    }
}
