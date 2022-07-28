<?php
namespace Magenest\PendingCod\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Inventory\Model\Source\Command\GetSourcesAssignedToStockOrderedByPriority;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Magenest\PendingCod\Model\ResourceModel\PendingOrder\CollectionFactory;
use Magenest\PendingCod\Model\ResourceModel\PendingOrderFactory as ResourceFactory;
use Magenest\PendingCod\Model\PendingOrderFactory;
use Magento\Sales\Model\ResourceModel\EntityAbstract;
use Psr\Log\LoggerInterface;

/**
 * Class EmailSenderHandler
 * @package Magenest\PendingCod\Model
 */
class EmailSenderHandler
{
    /**
     * Email sender model.
     *
     * @var Sender
     */
    protected $emailSender;

    /**
     * Entity resource model.
     *
     * @var EntityAbstract
     */
    protected $entityResource;

    /**
     * Entity collection model.
     *
     * @var AbstractCollection
     */
    protected $entityCollection;

    /**
     * Global configuration storage.
     *
     * @var ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magenest\PendingCod\Model\PendingOrderFactory
     */
    private $pendingOrderFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $sourceItemsBySku;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var TransportBuilder
     */
    private $_transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Escaper
     */
    private $_escaper;

    /**
     * @var ResourceFactory
     */
    private $pendingOrderResourceFactory;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var GetSourcesAssignedToStockOrderedByPriority
     */
    private $getSourcesAssignedToStockOrderedByPriority;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * EmailSenderHandler constructor.
     * @param Sender $emailSender
     * @param OrderRepositoryInterface $orderRepository
     * @param \Magenest\PendingCod\Model\PendingOrderFactory $pendingOrderFactory
     * @param CollectionFactory $collectionFactory
     * @param GetSourceItemsBySkuInterface $sourceItemsBySku
     * @param SourceRepositoryInterface $sourceRepository
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param ScopeConfigInterface $scopeConfig
     * @param Escaper $escaper
     * @param ResourceFactory $pendingOrderResourceFactory
     * @param StockResolverInterface $stockResolver
     * @param GetSourcesAssignedToStockOrderedByPriority $getSourcesAssignedToStockOrderedByPriority
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $globalConfig
     */
    public function __construct(
        Sender $emailSender,
        OrderRepositoryInterface $orderRepository,
        PendingOrderFactory $pendingOrderFactory,
        CollectionFactory $collectionFactory,
        GetSourceItemsBySkuInterface $sourceItemsBySku,
        SourceRepositoryInterface $sourceRepository,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        Escaper $escaper,
        ResourceFactory $pendingOrderResourceFactory,
        StockResolverInterface $stockResolver,
        GetSourcesAssignedToStockOrderedByPriority $getSourcesAssignedToStockOrderedByPriority,
        LoggerInterface $logger,
        ScopeConfigInterface $globalConfig
    ) {
        $this->emailSender = $emailSender;
        $this->orderRepository = $orderRepository;
        $this->pendingOrderFactory = $pendingOrderFactory;
        $this->collectionFactory = $collectionFactory;
        $this->globalConfig = $globalConfig;
        $this->sourceItemsBySku = $sourceItemsBySku;
        $this->sourceRepository = $sourceRepository;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->_escaper = $escaper;
        $this->pendingOrderResourceFactory = $pendingOrderResourceFactory;
        $this->stockResolver = $stockResolver;
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
        $this->_logger = $logger;
    }

    /**
     * Handles asynchronous email sending
     * @return void
     */
    public function sendEmails()
    {
        if ($this->globalConfig->getValue('sales_email/general/async_sending')) {
            $collection = $this->collectionFactory->create();
            $emailNeedSent = $collection->addFieldToFilter('email_sent', ['eq' => 0]);
            $emailNeedSent->walk([$this, 'getMailToSend']);
        }
    }

    /**
     * @param $row
     */
    public function getMailToSend($row) {
        $orderId = $row->getData('pending_cod_order_id');
        $order = $this->orderRepository->get($orderId);
        $incrementID = $order->getIncrementId();
        try {
            $storeCode = $order->getStore()->getData('code');
            $stock = $this->stockResolver->execute('store', $storeCode);
            $stockId = $stock->getStockId();
            $source = $this->getSourcesAssignedToStockOrderedByPriority->execute($stockId);
            $source = $source[0];
            $emailToSend = $source->getEmail();
            $this->sendEmailCodPending($emailToSend, $incrementID, $orderId);
        } catch (\Exception $exception) {
            $this->_logger->error($exception->getMessage());
            return;
        }
    }

    /**
     * @param $emailToSend
     * @param $incrementID
     * @param $orderId
     */
    protected function sendEmailCodPending($emailToSend, $incrementID, $orderId)
    {
        $this->inlineTranslation->suspend();
        try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $emailSender = $this->scopeConfig->getValue('magenest_email_sender/general/email_sender', $storeScope);
            $sender = [
                'name' => $this->_escaper->escapeHtml('Admin'),
                'email' => $this->_escaper->escapeHtml($emailSender),
            ];
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier('new_pending_cod_order_create')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(['orderId' => $incrementID])
                ->setFromByScope($sender)
                ->addTo($emailToSend)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            $this->updateAfterSendEmail($orderId);
            return;
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->_logger->error($e->getMessage());
            return;
        }
    }

    /**
     * @param $orderId
     */
    protected function updateAfterSendEmail($orderId)
    {
        try {
            $data = [
                'pending_cod_order_id' => $orderId,
                'email_sent' => 1,
                'send_email' => 1
            ];
            $model = $this->pendingOrderFactory->create();
            $model->setData($data);
            $resourceModel = $this->pendingOrderResourceFactory->create();
            $resourceModel->save($model);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            return;
        }
    }
}
