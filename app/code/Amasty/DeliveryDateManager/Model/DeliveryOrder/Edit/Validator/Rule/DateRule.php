<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\Rule;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\EditableConfigProvider;
use Magento\Framework\App\ScopeInterface;

class DateRule implements RuleInterface
{
    /**
     * @var EditableConfigProvider
     */
    private $configProvider;

    public function __construct(EditableConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDateOrder
     * @param int|ScopeInterface|null $store
     * @return bool
     */
    public function validate(DeliveryDateOrderInterface $deliveryDateOrder, $store = null): bool
    {
        $deliveryDate = $deliveryDateOrder->getDate();
        if ($deliveryDate) {
            $deliveryDate = strtotime($deliveryDate);
            $period = $this->configProvider->getPeriod($store);

            /**
             * DD - today                      the remaining time for delivering in seconds;
             * ceil(seconds / 60 / 60 / 24)    convert to days with round up;
             * Days > $period                  is Delivery Date can be edited;
             */
            return (ceil(($deliveryDate - time()) / 60 / 60 / 24) > $period);
        }

        return true;
    }
}
