<?php

namespace Magenest\Order\Plugin\Model\Order\Email\Sender;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

class OrderSender
{
    private Registry $registry;
    private ResourceConnection $resource;

    /**
     * @param Registry $registry
     * @param ResourceConnection $resource
     */
    public function __construct(
        Registry               $registry,
        ResourceConnection $resource
    ) {
        $this->registry = $registry;
        $this->resource = $resource;
    }

    /**
     * @param Order\Email\Sender\OrderSender $subject
     * @param $order
     * @param bool $notify
     * @param string $comment
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSend(
        Order\Email\Sender\OrderSender $subject,
        Order                          $order,
        bool                           $notify = true,
        string                         $comment = ''
    ) {
        $emailAdminStore = $this->getEmailByStoreId($order->getStoreId());
        $this->registry->register('email_admin_store', $emailAdminStore);
        return [$order, $notify, $comment];
    }

    /**
     * Get email admin by store id
     *
     * @param $storeId
     * @return string
     */
    protected function getEmailByStoreId($storeId): string
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            ['isch' => $connection->getTableName('ecombricks_store__inventory_stock_sales_channel')],
            ['email' => 'inventory_source.email']
        )
            ->join(
                ['store' => $connection->getTableName('store')],
                'store.code = isch.code',
                ['']
            )
            ->join(
                ['is' => $connection->getTableName('inventory_stock')],
                'is.stock_id = isch.stock_id',
                ['']
            )->join(
                ['issl' => $connection->getTableName('inventory_source_stock_link')],
                'issl.stock_id =is.stock_id',
                ['']
            )
            ->join(
                ['inventory_source' => $connection->getTableName('inventory_source')],
                'inventory_source.source_code = issl.source_code',
                ['']
            )->where('store.store_id = ?', $storeId)->limit(1);
        $email = $connection->fetchOne($select);
        return $email;
    }
}
