<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\Catalog\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Retrieve all children ids (regardless is_active configuration) of category
 * For getting only enabled category you should use @see \Magento\Catalog\Model\Category::getAllChildren
 *
 * @since v5.0.0
 */
class GetChildrenIds
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * GetChildrenIds constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     * @param bool                                        $withParentId
     * @param bool                                        $asArray
     * @return array|string
     */
    public function execute(CategoryInterface $category, bool $withParentId = true, bool $asArray = false)
    {
        $connection = $this->resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $tableName = $this->resourceConnection->getTableName('catalog_category_entity');

        $select = $connection
            ->select()
            ->from(
                $tableName,
                'entity_id'
            )->where(
                $connection->quoteIdentifier('path') . ' LIKE :c_path'
            );

        $bind = [
            'c_path' => $category->getPath() . '/%',
        ];

        $result = $connection->fetchCol($select, $bind);

        if ($withParentId) {
            $myId = [$category->getId()];
            $result = array_merge($myId, $result);
        }

        return $asArray ? $result : implode(',', $result);
    }
}
