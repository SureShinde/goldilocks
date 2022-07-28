<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Model\InventorySales\ResourceModel;

/**
 * Get store code by store ID
 */
class GetStoreCodeByStoreId
{

    /**
     * Connection provider
     *
     * @var \Ecombricks\Common\Model\ResourceModel\ConnectionProvider
     */
    protected $connectionProvider;

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Model\ResourceModel\ConnectionProvider $connectionProvider
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Model\ResourceModel\ConnectionProvider $connectionProvider
    )
    {
        $this->connectionProvider = $connectionProvider;
    }

    /**
     * Execute
     *
     * @param int $storeId
     * @return string|null
     */
    public function execute(int $storeId): ?string
    {
        $connection = $this->connectionProvider->getConnection();
        $select = $connection->select()
            ->from($this->connectionProvider->getTable('store'), 'code')
            ->where('store_id = ?', $storeId);
        $result = $connection->fetchOne($select);
        return (false === $result) ? null : $result;
    }

}
