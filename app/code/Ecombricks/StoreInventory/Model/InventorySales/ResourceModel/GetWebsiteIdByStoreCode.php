<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Model\InventorySales\ResourceModel;

/**
 * Get website ID by store code
 */
class GetWebsiteIdByStoreCode
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
     * @param string $storeCode
     * @return int|null
     */
    public function execute(string $storeCode): ?int
    {
        $connection = $this->connectionProvider->getConnection();
        $select = $connection->select()
            ->from($this->connectionProvider->getTable('store'), 'website_id')
            ->where('code = ?', $storeCode);
        $result = $connection->fetchOne($select);
        return (false === $result) ? null : $result;
    }

}
