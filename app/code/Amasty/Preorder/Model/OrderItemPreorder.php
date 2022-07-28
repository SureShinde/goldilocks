<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model;

use Amasty\Preorder\Api\Data\OrderItemInformationInterface;
use Amasty\Preorder\Model\ResourceModel\OrderItemPreorder as OrderItemPreorderResource;
use Magento\Framework\Model\AbstractModel;

class OrderItemPreorder extends AbstractModel implements OrderItemInformationInterface
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(OrderItemPreorderResource::class);
    }

    public function isPreorder(): bool
    {
        return (bool) $this->getData(self::PREORDER_FLAG);
    }

    public function setIsPreorder(bool $isPreorder): OrderItemInformationInterface
    {
        return $this->setData(self::PREORDER_FLAG, $isPreorder);
    }

    public function getNote(): ?string
    {
        return $this->getData(self::NOTE);
    }

    public function setNote(string $note): OrderItemInformationInterface
    {
        return $this->setData(self::NOTE, $note);
    }
}
