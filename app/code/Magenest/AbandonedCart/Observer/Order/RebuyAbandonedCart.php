<?php

namespace Magenest\AbandonedCart\Observer\Order;

use Magenest\AbandonedCart\Model\AbandonedCart;
use Magenest\AbandonedCart\Model\AbandonedCartFactory;
use Magenest\AbandonedCart\Model\Config\Source\Mail as EmailStatus;
use Magenest\AbandonedCart\Model\LogContent;
use Magenest\AbandonedCart\Model\LogContentFactory;
use Magenest\AbandonedCart\Model\ResourceModel\LogContent\Collection;
use Magenest\AbandonedCart\Model\ResourceModel\LogContent\CollectionFactory as LogContentCollection;
use Magenest\AbandonedCart\Model\ResourceModel\LogContent  as LogContentResource;
use Magenest\AbandonedCart\Model\ResourceModel\AbandonedCart\CollectionFactory as AbCollection;
use Magenest\AbandonedCart\Model\Rule;
use Magenest\AbandonedCart\Model\RuleFactory;
use Magenest\AbandonedCart\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Magenest\AbandonedCart\Model\ResourceModel\AbandonedCart as AbResourceModel;
use Magento\Sales\Model\Order\Item;
use Psr\Log\LoggerInterface as Logger;

class RebuyAbandonedCart implements \Magento\Framework\Event\ObserverInterface
{
    /** @var LogContentFactory  */
    protected $_logContentFactory;

    /** @var LogContentCollection */
    protected $_logContentCollection;

    /** @var LogContentResource */
    protected $_logContentResource;

    /** @var RuleFactory  */
    protected $_ruleFactory;

    /** @var RuleCollection */
    protected $_ruleCollection;

    /** @var AbandonedCartFactory  */
    protected $_abandonedCartFactory;

    /** @var AbCollection */
    protected $_abCollection;

    /** @var AbResourceModel */
    protected $_abResource;

    /** @var Logger */
    protected $_logger;

    /** @var Session $checkoutSession */
    protected $_checkoutSession;

    /**
     * RebuyAbandonedCart constructor.
     *
     * @param LogContentFactory $logContentFactory
     * @param RuleFactory $ruleFactory
     * @param AbandonedCartFactory $abandonedCartFactory
     * @param AbCollection $abCollection
     * @param AbResourceModel $abResource
     * @param RuleCollection $ruleCollection
     * @param LogContentCollection $logContentCollection
     * @param LogContentResource $logContentResource
     * @param Logger $logger
     * @param Session $checkoutSession
     */
    public function __construct(
        LogContentFactory $logContentFactory,
        RuleFactory $ruleFactory,
        AbandonedCartFactory $abandonedCartFactory,
        AbCollection $abCollection,
        AbResourceModel $abResource,
        RuleCollection $ruleCollection,
        LogContentCollection $logContentCollection,
        LogContentResource $logContentResource,
        Logger $logger,
        Session $checkoutSession
    ) {
        $this->_logContentFactory    = $logContentFactory;
        $this->_ruleFactory          = $ruleFactory;
        $this->_abandonedCartFactory = $abandonedCartFactory;
        $this->_abCollection = $abCollection;
        $this->_abResource = $abResource;
        $this->_ruleCollection = $ruleCollection;
        $this->_logContentCollection = $logContentCollection;
        $this->_logContentResource = $logContentResource;
        $this->_logger = $logger;
        $this->_checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($order->getState() === Order::STATE_NEW) {
            $ruleIds = $this->_ruleCollection->create()->getAllIds();
            if (!empty($ruleIds)) {
                /** @var Collection $logContentModels */
                $logContentModels = $this->_logContentCollection->create()
                    ->addFieldToFilter('rule_id', ['IN' => $ruleIds])
                    ->addFieldToFilter('quote_id', $order->getQuoteId())
                    ->addFieldToFilter('status', EmailStatus::STATUS_QUEUED);
                /** @var LogContent $logContent */
                foreach ($logContentModels as $logContent) {
                    if ($logContent->getId()) {
                        $logContent->addData([
                            'status'     => EmailStatus::STATUS_CANCELLED,
                            'log'        => 'Customer Re-bought Abandoned Cart',
                            'is_restore' => 2
                        ]);
                        try {
                            $this->_logContentResource->save($logContent);
                        } catch (\Exception $e) {
                            $this->_logger->critical($e->getMessage());
                        }
                    }
                }
                $abandonedCartModels = $this->_abCollection->create()->addFieldToFilter(
                    'quote_id',
                    $order->getQuoteId()
                );
                /** @var AbandonedCart $abandonedCartModel */
                $rule_id = $this->_checkoutSession->getData('ab_rule');
                foreach ($abandonedCartModels as $abandonedCartModel) {
                    $data = [
                        'placed' => $order->getId(),
                        'status' => $rule_id ? AbandonedCart::STATUS_RECOVERED : AbandonedCart::STATUS_CONVERTED,
                        'rule_id' => $rule_id
                    ];
                    $abandonedCartModel->addData($data);
                    try {
                        $this->_abResource->save($abandonedCartModel);
                    } catch (\Exception $e) {
                        $this->_logger->critical($e->getMessage());
                    }
                }
            }
        }
    }
}
