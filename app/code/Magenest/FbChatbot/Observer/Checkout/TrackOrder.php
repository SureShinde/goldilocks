<?php
namespace Magenest\FbChatbot\Observer\Checkout;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class TrackOrder implements ObserverInterface {

    /**
     * @var ResourceConnection
     */
    protected $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    public function execute(Observer $observer)
    {
        $quote = $observer->getData('quote');
        $order = $observer->getData('order');
        $senderId = $quote->getData('sender_id');
        if(!empty($senderId)){
            $connection  = $this->resource->getConnection();
            $salesOrderGridTable  = $connection->getTableName('sales_order_grid');
            $connection->update($salesOrderGridTable, ['ordered_bot' => 1], ["entity_id = ?" => $order->getId()]);
        }
    }
}
