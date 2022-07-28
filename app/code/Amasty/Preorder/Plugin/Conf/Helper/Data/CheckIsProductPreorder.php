<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Conf\Helper\Data;

use Amasty\Conf\Helper\Data as Subject;
use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Product\GetPreorderInformation;
use Magento\Catalog\Api\Data\ProductInterface;

class CheckIsProductPreorder
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    public function __construct(ConfigProvider $configProvider, GetPreorderInformation $getPreorderInformation)
    {
        $this->configProvider = $configProvider;
        $this->getPreorderInformation = $getPreorderInformation;
    }

    public function afterIsPreorderEnabled(Subject $subject, bool $result, ProductInterface $product): bool
    {
        return $this->configProvider->isEnabled()
            && $this->getPreorderInformation->execute($product)->isPreorder();
    }
}
