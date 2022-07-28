<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Sales\Model\ResourceModel\Order\Grid\Collection;

use Amasty\Preorder\Model\ResourceModel\Order\Grid\Collection\JoinPreorderData as JoinPreorderDataResource;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class JoinPreorderData
{
    /**
     * @var JoinPreorderDataResource
     */
    private $joinPreorderData;

    public function __construct(JoinPreorderDataResource $joinPreorderData)
    {
        $this->joinPreorderData = $joinPreorderData;
    }

    public function beforeLoad(Collection $subject): void
    {
        $this->joinPreorderData->execute($subject);
    }
}
