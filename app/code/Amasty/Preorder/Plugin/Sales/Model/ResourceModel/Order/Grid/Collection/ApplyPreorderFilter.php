<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Sales\Model\ResourceModel\Order\Grid\Collection;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Amasty\Preorder\Model\ResourceModel\Order\Grid\Collection\JoinPreorderData;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class ApplyPreorderFilter
{
    /**
     * @var JoinPreorderData
     */
    private $joinPreorderData;

    public function __construct(JoinPreorderData $joinPreorderData)
    {
        $this->joinPreorderData = $joinPreorderData;
    }

    public function aroundAddFieldToFilter(
        Collection $subject,
        callable $proceed,
        $field,
        $condition = null
    ): Collection {
        if (is_string($field)
            && $field === OrderInformationInterface::PREORDER_FLAG
            && isset($condition['eq'])
            && $condition['eq'] == 0
        ) {
            $this->joinPreorderData->execute($subject);
            $proceed(
                [OrderInformationInterface::PREORDER_FLAG, OrderInformationInterface::PREORDER_FLAG],
                [['null' => true], ['eq' => 0]]
            );
        } else {
            $proceed($field, $condition);
        }

        return $subject;
    }
}
