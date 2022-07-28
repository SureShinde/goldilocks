<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\ViewModel;

use Amasty\PreOrderAnalytic\Model\IsPreorderOrdersExist;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class PreorderAnalytic implements ArgumentInterface
{
    /**
     * @var IsPreorderOrdersExist
     */
    private $isPreorderOrdersExist;

    public function __construct(IsPreorderOrdersExist $isPreorderOrdersExist)
    {
        $this->isPreorderOrdersExist = $isPreorderOrdersExist;
    }

    public function isDataExist(): bool
    {
        return $this->isPreorderOrdersExist->execute();
    }
}
