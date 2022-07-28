<?php

declare(strict_types=1);

namespace Amasty\Preorder\ViewModel\Product\View;

use Amasty\Preorder\Api\Data\ProductInformationInterface;
use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Product\GetPreorderInformation;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Preorder implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Registry $registry,
        GetPreorderInformation $getPreorderInformation,
        ConfigProvider $configProvider
    ) {
        $this->registry = $registry;
        $this->getPreorderInformation = $getPreorderInformation;
        $this->configProvider = $configProvider;
    }

    public function getCurrentProduct(): ProductInterface
    {
        return $this->registry->registry('current_product');
    }

    public function getPreorderInformation(ProductInterface $product): ProductInformationInterface
    {
        return $this->getPreorderInformation->execute($product);
    }

    public function getPreorderNotePosition(): string
    {
        return $this->configProvider->getPreorderNotePosition();
    }

    public function getOriginalNote(ProductInterface $product): Phrase
    {
        return $product->isAvailable() ? __('In stock') : __('Out of stock');
    }

    public function escapeQuote(string $string): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return addslashes($string);
    }
}
