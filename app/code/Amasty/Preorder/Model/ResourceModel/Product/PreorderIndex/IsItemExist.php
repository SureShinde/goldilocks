<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\ResourceModel\Product\PreorderIndex;

use Amasty\Preorder\Model\ResourceModel\Product\PreorderIndex;

class IsItemExist
{
    /**
     * @var PreorderIndex
     */
    private $preorderIndex;

    public function __construct(PreorderIndex $preorderIndex)
    {
        $this->preorderIndex = $preorderIndex;
    }

    public function execute(int $productId, int $websiteId): bool
    {
        $equalsTemplate = '%s = ?';

        $select = $this->preorderIndex->getConnection()->select()->from(
            $this->preorderIndex->getTableName(PreorderIndex::MAIN_TABLE),
            [PreorderIndex::PRODUCT_ID]
        )->where(
            sprintf($equalsTemplate, PreorderIndex::PRODUCT_ID),
            $productId
        )->where(
            sprintf($equalsTemplate, PreorderIndex::WEBSITE_ID),
            $websiteId
        );

        return (bool) $this->preorderIndex->getConnection()->fetchOne($select);
    }
}
