<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order;

use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\OrderPreorder\Query\GetByOrderIdInterface;
use Amasty\Preorder\Model\OrderPreorderFactory;
use Amasty\Preorder\Model\Utils\StripTags;
use Magento\Framework\Exception\NoSuchEntityException;

class GetWarning
{
    /**
     * @var GetByOrderIdInterface
     */
    private $getByOrderId;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StripTags
     */
    private $stripTags;

    public function __construct(
        GetByOrderIdInterface $getByOrderId,
        ConfigProvider $configProvider,
        StripTags $stripTags
    ) {
        $this->getByOrderId = $getByOrderId;
        $this->configProvider = $configProvider;
        $this->stripTags = $stripTags;
    }

    public function execute(int $orderId): string
    {
        try {
            $orderPreorder = $this->getByOrderId->execute($orderId);
            $warning = $this->stripTags->execute($orderPreorder->getWarning(), StripTags::ALLOWED_TAGS);
        } catch (NoSuchEntityException $e) {
            $warning = '';
        }

        return $warning;
    }
}
