<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order\ProcessNew;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\OrderPreorder\Command\SaveInterface;
use Amasty\Preorder\Model\OrderPreorder\Query\GetNewInterface;
use Amasty\Preorder\Model\Utils\StripTags;
use Magento\Sales\Api\Data\OrderInterface;

class SaveOrderFlag implements SaveOrderFlagInterface
{
    /**
     * @var GetNewInterface
     */
    private $getNew;

    /**
     * @var SaveInterface
     */
    private $save;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StripTags
     */
    private $stripTags;

    public function __construct(
        GetNewInterface $getNew,
        SaveInterface $save,
        ConfigProvider $configProvider,
        StripTags $stripTags
    ) {
        $this->getNew = $getNew;
        $this->save = $save;
        $this->configProvider = $configProvider;
        $this->stripTags = $stripTags;
    }

    public function execute(OrderInterface $order): void
    {
        $orderFlag = $this->getNew->execute([
            OrderInformationInterface::ORDER_ID => (int) $order->getEntityId(),
            OrderInformationInterface::PREORDER_FLAG => true,
            OrderInformationInterface::WARNING => $this->stripTags->execute(
                $this->configProvider->getOrderPreorderWarning(),
                StripTags::ALLOWED_TAGS
            )
        ]);
        $this->save->execute($orderFlag);
    }
}
