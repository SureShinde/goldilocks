<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Sales\Order\Email;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\InfoOutput;
use Magento\Framework\View\Element\Template\Context;

/**
 * Add delivery date info to email
 *
 * Output via plugin, @see \Amasty\DeliveryDateManager\Plugin\Sales\Block\Items\AbstractItems\AddBlockToEmail
 */
class Deliverydate extends \Magento\Framework\View\Element\Template
{
    /**
     * @var InfoOutput
     */
    private $infoOutput;

    public function __construct(
        Context $context,
        InfoOutput $infoOutput,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->infoOutput = $infoOutput;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Amasty_DeliveryDateManager::email.phtml');
    }

    /**
     * @return DeliveryDateOrderInterface
     */
    public function getDeliveryDate(): DeliveryDateOrderInterface
    {
        return $this->getData('delivery_date');
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder(): \Magento\Sales\Api\Data\OrderInterface
    {
        return $this->getData('order');
    }

    /**
     * @return array array(array('label' => 'string', 'value' => 'string'), ...)
     */
    public function getList(): array
    {
        return $this->infoOutput->getOutput(
            $this->getDeliveryDate(),
            $this->getPlace(),
            (int)$this->getOrder()->getStoreId()
        );
    }

    /**
     * Place of use block. Values
     * @see \Amasty\DeliveryDateManager\Model\Config\Source\IncludeInto or
     * @see \Amasty\DeliveryDateManager\Model\Config\Source\Show
     * @return string
     */
    public function getPlace(): string
    {
        return (string)$this->getData('place');
    }
}
