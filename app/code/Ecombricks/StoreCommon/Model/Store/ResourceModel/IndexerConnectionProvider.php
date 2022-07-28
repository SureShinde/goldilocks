<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreCommon\Model\Store\ResourceModel;

/**
 * Indexer resource connection provider
 */
class IndexerConnectionProvider extends \Ecombricks\Common\Model\ResourceModel\ConnectionProvider
{

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\DB\Sql\ExpressionFactory $expressionFactory
     * @param string $resourceName
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\DB\Sql\ExpressionFactory $expressionFactory,
        string $resourceName = 'indexer'
    )
    {
        parent::__construct(
            $resourceConnection,
            $expressionFactory,
            $resourceName
        );
    }

}
