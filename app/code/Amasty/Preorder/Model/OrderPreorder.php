<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Amasty\Preorder\Model\ResourceModel\OrderPreorder as OrderPreorderResource;
use Magento\Framework\Model\AbstractModel;

class OrderPreorder extends AbstractModel implements OrderInformationInterface
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(OrderPreorderResource::class);
    }

    public function isPreorder(): bool
    {
        return (bool) $this->getData(self::PREORDER_FLAG);
    }

    public function setIsPreorder(bool $isPreorder): OrderInformationInterface
    {
        return $this->setData(self::PREORDER_FLAG, $isPreorder);
    }

    public function getWarning(): ?string
    {
        return $this->getData(self::WARNING);
    }

    public function setWarning(string $warning): OrderInformationInterface
    {
        return $this->setData(self::WARNING, $warning);
    }
}
