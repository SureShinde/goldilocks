<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Sales\Model\ResourceModel\Order\Grid\Collection;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Amasty\Preorder\Model\ResourceModel\Order\Grid\Collection\JoinPreorderData;
use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\RegularFilter;

class JoinPreorderTableToExport
{
    /**
     * @var JoinPreorderData
     */
    private $joinPreorderData;

    public function __construct(JoinPreorderData $joinPreorderData)
    {
        $this->joinPreorderData = $joinPreorderData;
    }

    /**
     * @param RegularFilter $subject
     * @param Collection $collection
     * @param Filter $filter
     * @return void
     */
    public function beforeApply(RegularFilter $subject, Collection $collection, Filter $filter): void
    {
        if ($filter->getField() === OrderInformationInterface::PREORDER_FLAG) {
            $this->joinPreorderData->execute($collection);
        }
    }
}
