<?php

namespace Magenest\AbandonedCart\Block\Adminhtml\Chart;

use Magenest\AbandonedCart\Model\AbandonedCart as AbandonedModel;
use Magenest\AbandonedCart\Model\LogContent;
use Magento\Quote\Model\Quote;
use Magento\Framework\DB\Select;
use Magento\Sales\Model\Order;
use Magenest\AbandonedCart\Model\ResourceModel\LogContent\CollectionFactory as LogContentCollection;
use Magenest\AbandonedCart\Model\ResourceModel\AbandonedCart\CollectionFactory as AbandonedCollection;

class AbandonedCart extends \Magenest\AbandonedCart\Block\Adminhtml\Chart\AbstractChart
{
    protected $abandonedCarts;

    protected $customerAbandonedCarts;

    /** @var \Magenest\AbandonedCart\Model\AbandonedCartFactory $abandonedCartFactory */
    protected $abandonedCartFactory;

    protected $guestAbandonedCarts;

    protected $nonAbandonedCarts;

    protected $carts;

    /** @var \Magento\Quote\Model\QuoteFactory $quoteFactory */
    protected $quoteFactory;

    protected $repurchasedAbandonedCarts;

    protected $nonRepurchasedAbandonedCarts;

    /** @var \Magenest\AbandonedCart\Model\LogContentFactory $_logContentFactory */
    protected $_logContentFactory;

    /** @var \Magento\Sales\Model\OrderFactory $_orderFactory */
    protected $_orderFactory;

    /** @var LogContentCollection $_logCollection */
    protected $_logCollection;

    /** @var AbandonedCollection $_abandonedCollection */
    protected $_abandonedCollection;


    /**
     * AbandonedCart constructor.
     * @param \Magenest\AbandonedCart\Model\LogContentFactory $logContentFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param AbandonedCartFactory $abandonedCartFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param LogContentCollection $logCollection
     * @param AbandonedCollection $abandonedCollection
     * @param array $data
     */
    public function __construct(
        \Magenest\AbandonedCart\Model\LogContentFactory $logContentFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magenest\AbandonedCart\Model\AbandonedCartFactory $abandonedCartFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Backend\Block\Template\Context $context,
        LogContentCollection $logCollection,
        AbandonedCollection $abandonedCollection,
        array $data = []
    ) {
        $this->_logContentFactory = $logContentFactory;
        $this->_orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        $this->abandonedCartFactory = $abandonedCartFactory;
        $this->_logCollection = $logCollection;
        $this->_abandonedCollection = $abandonedCollection;
        parent::__construct($context, $data);
    }

    public function getAbandonedCarts()
    {
        if ($this->abandonedCarts) {
            return $this->abandonedCarts;
        }
        $abadonedCarts = $this->getCustomerAbandonedCarts() + $this->getGuestAbandonedCarts();
        $this->abandonedCarts = $abadonedCarts;
        return $this->abandonedCarts;
    }

    public function getCustomerAbandonedCarts()
    {
        if ($this->customerAbandonedCarts) {
            return $this->customerAbandonedCarts;
        }
        $cartModel = $this->_abandonedCollection->create();
        $from = $this->getFromDate();
        $to = $this->getToDate();
        $collection = $cartModel
            ->addFieldToFilter('type', 'customer')
            ->getSelect()
            ->reset(Select::COLUMNS)
            ->where("created_at >= '" . $from . "' AND created_at <= '" . $to . "' AND status = ?", AbandonedModel::STATUS_ABANDONED)
            ->columns([
                'id'
            ]);
        $rows = $cartModel->getResource()->getConnection()->fetchAll($collection);
        $this->customerAbandonedCarts = count($rows);
        return $this->customerAbandonedCarts;
    }

    public function getGuestAbandonedCarts()
    {
        if ($this->guestAbandonedCarts) {
            return $this->guestAbandonedCarts;
        }
        $cartModel = $this->_abandonedCollection->create();
        $from = $this->getFromDate();
        $to = $this->getToDate();
        $collection = $cartModel
            ->addFieldToFilter('type', 'guest')
            ->getSelect()
            ->reset(Select::COLUMNS)
            ->where("created_at >= '" . $from . "' AND created_at <= '" . $to . "' AND status = ?", AbandonedModel::STATUS_ABANDONED)
            ->columns([
                'id'
            ]);
        $rows = $cartModel->getResource()->getConnection()->fetchAll($collection);
        $this->guestAbandonedCarts = count($rows);
        return $this->guestAbandonedCarts;
    }

    public function getAbandonedCartData()
    {
        return [
            'Abandoned' => $this->getAbandonedCarts(),
            'Completed' => $this->getNonAbadonedCarts()
        ];
    }

    public function getNonAbadonedCarts()
    {
        if ($this->nonAbandonedCarts) {
            return $this->nonAbandonedCarts;
        }
        $this->nonAbandonedCarts = $this->getCarts() - $this->getAbandonedCarts();
        return $this->nonAbandonedCarts;
    }

    public function getCarts()
    {
        if ($this->carts) {
            return $this->carts;
        }
        $from = $this->getFromDate();
        $to = $this->getToDate();
        $quoteModel = $this->quoteFactory->create();
        $carts = $quoteModel->getCollection()
            ->getSelect()
            ->where("created_at >= '" . $from . "' AND created_at <= '" . $to . "'");
        $rows = $quoteModel->getResource()->getConnection()->fetchAll($carts);
        $this->carts = count($rows);
        return $this->carts;
    }

    public function getGuestAbandonedCartData()
    {
        return [
            'Guest' => $this->getGuestAbandonedCarts(),
            'Customer' => $this->getCustomerAbandonedCarts()
        ];
    }

    public function getRepurchasedCartData()
    {
        return [
            'Repurchased' => $this->getRepurchasedAbandonedCarts(),
            'Abandoned' => $this->getNonRepurchasedAbandonedCarts()
        ];
    }

    public function getRepurchasedAbandonedCarts()
    {
        $repurchasedAbandonedCarts = $this->_abandonedCollection->create();
        $from = $this->getFromDate();
        $to = $this->getToDate();
        $collections = $repurchasedAbandonedCarts
            ->getSelect()
            ->where("created_at >= '" . $from . "' AND created_at <= '" . $to . "' AND status > ?", AbandonedModel::STATUS_ABANDONED);
        $rows = $repurchasedAbandonedCarts->getResource()->getConnection()->fetchAll($collections);
        return count($rows);
    }

    public function getNonRepurchasedAbandonedCarts()
    {
        $repurchasedAbandonedCarts = $this->_abandonedCollection->create();
        $from = $this->getFromDate();
        $to = $this->getToDate();
        $collections = $repurchasedAbandonedCarts
            ->getSelect()
            ->where("created_at >= '" . $from . "' AND created_at <= '" . $to . "' AND status = ?", AbandonedModel::STATUS_ABANDONED)->__toString();
        $rows = $repurchasedAbandonedCarts->getResource()->getConnection()->fetchAll($collections);
        return count($rows);
    }

    public function getAbandonedCartLineChart()
    {
        $abandonedCart = $this->_abandonedCollection->create();
        $from = $this->getFromDate();
        $to = $this->getToDate();
        $select = $abandonedCart->getSelect()->reset(Select::COLUMNS)
            ->group(
                'CAST(main_table.created_at AS DATE)'
            )->order(
                'CAST(main_table.created_at AS DATE) ASC'
            )->where("created_at >= '" . $from . "' AND created_at <= '" . $to . "' AND status = ?", AbandonedModel::STATUS_ABANDONED)
            ->columns([
                'COUNT(main_table.id) as count',
                'created_at' => new \Zend_Db_Expr('CAST(main_table.created_at AS DATE)')
            ]);
        $rows = $abandonedCart->getResource()->getConnection()->fetchAll($select);
        return $rows;
    }

    public function getTotalRestore()
    {
        $logContent = $this->_logCollection->create();
        $from = $this->getFromDate();
        $to = $this->getToDate();
        $logContentCollection = $logContent->getSelect()->where(
            "created_at >= '" . $from . "' AND created_at <= '" . $to . "' AND main_table.is_restore like 1"
        );
        $rows = $logContent->getResource()->getConnection()->fetchAll($logContentCollection);
        $result = count($rows);
        return $result;
    }

    public function getTotalOrder()
    {
        $abandonedCart = $this->_abandonedCollection->create();
        $from = $this->getFromDate();
        $to = $this->getToDate();
        $abandonedCartCollection = $abandonedCart
            ->getSelect()
            ->where("created_at >= '" . $from . "' AND created_at <= '" . $to . "' AND status > ? ", AbandonedModel::STATUS_ABANDONED);
        $rows = $abandonedCart->getResource()->getConnection()->fetchAll($abandonedCartCollection);
        $result = count($rows);
        return $result;
    }

    public function getGrandTotal()
    {
        $order = $this->_orderFactory->create();
        $collection = $order->getCollection();
        $abandonedCartTable = $collection->getTable('magenest_abacar_list');
        $from = $this->getFromDate();
        $to = $this->getToDate();
        $select = $collection->getSelect()->reset(Select::COLUMNS)
            ->joinLeft(
                ['a' => $abandonedCartTable],
                'main_table.entity_id = a.placed',
                []
            )->where(
                "main_table.`created_at` >= '" . $from . "' AND main_table.`created_at` <= '" . $to . "' AND a.status > ? ",
                AbandonedModel::STATUS_ABANDONED
            )->columns([
                'SUM(main_table.base_grand_total) as totals',
                'created_at' => new \Zend_Db_Expr('CAST(main_table.created_at AS DATE)'),
                'base_currency_code'
            ])->__toString();
        $rows = $order->getResource()->getConnection()->fetchAll($select);
        return $rows[0]['totals'] . ' ' . $rows[0]['base_currency_code'];
    }


    /**
     * @return int|mixed
     */
    public function getTotalProduct()
    {
        $collection = $this->_abandonedCollection->create();
        $from = $this->getFromDate();
        $to = $this->getToDate();
        $total = 0;
        $collection->addFieldToFilter('main_table.created_at', ['gteq' => $from])
            ->addFieldToFilter('main_table.created_at', ['lteq' => $to])
            ->addFieldToSelect('quote_id');
        $collection->getSelect()
            ->joinLeft(
                ['quote_core' => $collection->getTable('quote')],
                'quote_core.entity_id = main_table.quote_id',
                'quote_core.items_qty as total_items'
            )->distinct();
        if ($collection->count()) {
            foreach ($collection->getColumnValues('total_items') as $value) {
                $total += $value;
            };
        }
        return $total;
    }

    public function getTotalRecoveredProduct()
    {
        $abandonedCollection = $this->_abandonedCollection->create();
        $from = $this->getFromDate();
        $to = $this->getToDate();
        $abandonedCollection->addFieldToFilter('main_table.created_at', ['gteq' => $from])
            ->addFieldToFilter('main_table.created_at', ['lteq' => $to])
            ->addFieldToFilter('main_table.status', AbandonedModel::STATUS_RECOVERED);
        $abandonedCollection->getSelect()->joinLeft(
            ['q' => $abandonedCollection->getTable('quote')],
            'q.entity_id = main_table.quote_id',
            ['q.items_qty']
        );
        return array_sum($abandonedCollection->getColumnValues('items_qty'));
    }

    public function getTotalAbandonedProduct()
    {
        return $this->getTotalProduct() - $this->getTotalRecoveredProduct();
    }
}
