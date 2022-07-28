<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\ResourceModel;

use Magento\Framework\DB\Select;

class ApplyFilterParams
{
    /**
     * @var array
     */
    private $mapTemplates = [
        'created_at' => 'DATE(%s.%s)'
    ];

    public function execute(Select $select, array $params): void
    {
        $fromPart = $select->getPart(Select::FROM);
        $mainTable = array_key_first($fromPart);
        foreach ($params as $fieldName => $condition) {
            $select->where($select->getConnection()->prepareSqlCondition(
                sprintf(
                    $this->mapTemplates[$fieldName] ?? '%s.%s',
                    $mainTable,
                    $fieldName
                ),
                $condition
            ));
        }
    }
}
