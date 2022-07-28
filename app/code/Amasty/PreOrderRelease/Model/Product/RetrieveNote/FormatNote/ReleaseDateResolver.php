<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\Product\RetrieveNote\FormatNote;

use Amasty\Preorder\Model\Product\RetrieveNote\FormatNote\CustomResolverInterface;
use Amasty\PreOrderRelease\Model\ConfigProvider;
use Amasty\PreOrderRelease\Model\Product\GetReleaseDate;
use IntlDateFormatter;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ReleaseDateResolver implements CustomResolverInterface
{
    /**
     * @var GetReleaseDate
     */
    private $getReleaseDate;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        GetReleaseDate $getReleaseDate,
        TimezoneInterface $timezone,
        ConfigProvider $configProvider
    ) {
        $this->getReleaseDate = $getReleaseDate;
        $this->timezone = $timezone;
        $this->configProvider = $configProvider;
    }

    /**
     * @param ProductInterface|Product $product
     * @return string
     */
    public function execute(ProductInterface $product): string
    {
        return $this->timezone->formatDateTime(
            $this->getReleaseDate->execute($product),
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::NONE,
            null,
            null,
            $this->configProvider->getReleaseDateFormat()
        );
    }
}
