<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Sales\Order\Info;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\Config\Source\Show;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\EditableOnFront;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\InfoOutput;
use Magento\Framework\View\Element\Template\Context;

/**
 * Add delivery date info to order view page in front customer account
 *
 * Output via plugin, @see \Amasty\DeliveryDateManager\Plugin\Sales\Block\Items\AbstractItems\AddBlockToOrder
 */
class Deliverydate extends \Magento\Framework\View\Element\Template
{
    /**
     * @var EditableOnFront
     */
    private $editableOnFront;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var InfoOutput
     */
    private $infoOutput;

    public function __construct(
        Context $context,
        EditableOnFront $editableOnFront,
        \Magento\Framework\App\Http\Context $httpContext,
        InfoOutput $infoOutput,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->editableOnFront = $editableOnFront;
        $this->httpContext = $httpContext;
        $this->infoOutput = $infoOutput;
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('Amasty_DeliveryDateManager::info.phtml');
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        return $this->infoOutput->getOutput(
            $this->getDeliveryDate(),
            Show::ORDER_INFO,
            (int)$this->getOrder()->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getEditUrl(): string
    {
        $orderId = $this->getOrder()->getId();
        if ($this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)) {
            return $this->getUrl('deliverydate/deliverydate/edit', ['order_id' => $orderId]);
        }

        return $this->getUrl('deliverydate/guest/edit', ['order_id' => $orderId]);
    }

    /**
     * @param string $field code
     *
     * @return bool
     */
    public function isFieldEditable(string $field): bool
    {
        if ($field === 'date') {
            return $this->editableOnFront->validate($this->getDeliveryDate());
        }

        return false;
    }

    /**
     * @return DeliveryDateOrderInterface
     */
    private function getDeliveryDate(): DeliveryDateOrderInterface
    {
        return $this->getData('delivery_date');
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    private function getOrder(): \Magento\Sales\Api\Data\OrderInterface
    {
        return $this->getData('order');
    }
}
