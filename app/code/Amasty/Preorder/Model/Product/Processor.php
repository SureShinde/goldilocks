<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product;

use Amasty\Preorder\Api\Data\ProductInformationInterface;
use Amasty\Preorder\Api\Data\ProductInformationInterfaceFactory;
use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Product\Detect\IsProductPreorderInterface;
use Amasty\Preorder\Model\Product\RetrieveNote\GetCartLabel;
use Amasty\Preorder\Model\Product\RetrieveNote\GetNote;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\StoreManagerInterface;

class Processor
{
    /**
     * @var IsProductPreorderInterface
     */
    private $isProductPreorder;

    /**
     * @var ProductInformationInterfaceFactory
     */
    private $preorderProductInformationFactory;

    /**
     * @var GetCartLabel
     */
    private $getCartLabel;

    /**
     * @var GetNote
     */
    private $getNote;

    /**
     * @var ExtensionAttributeRegistry
     */
    private $extensionAttributeRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        IsProductPreorderInterface $isProductPreorder,
        ProductInformationInterfaceFactory $preorderProductInformationFactory,
        GetCartLabel $getCartLabel,
        GetNote $getNote,
        ExtensionAttributeRegistry $extensionAttributeRegistry,
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider
    ) {
        $this->isProductPreorder = $isProductPreorder;
        $this->preorderProductInformationFactory = $preorderProductInformationFactory;
        $this->getCartLabel = $getCartLabel;
        $this->getNote = $getNote;
        $this->extensionAttributeRegistry = $extensionAttributeRegistry;
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
    }

    /**
     * @param ProductInterface[] $productsArray
     * @return void
     */
    public function execute(array $productsArray): void
    {
        foreach ($productsArray as $product) {
            if ($product->getExtensionAttributes()->getPreorderInfo()) {
                continue;
            }

            $websiteId = (int) $this->storeManager->getStore($product->getStoreId())->getWebsiteId();
            $preorderProductInformation = $this->extensionAttributeRegistry->get(
                $product->getData('sku'),
                $websiteId
            );
            if ($preorderProductInformation === null) {
                $preorderProductInformation = $this->initProductPreorderInformation($product);
                $this->extensionAttributeRegistry->set(
                    $product->getData('sku'),
                    $websiteId,
                    $preorderProductInformation
                );
            }

            $product->getExtensionAttributes()->setPreorderInfo($preorderProductInformation);
        }
    }

    private function initProductPreorderInformation(ProductInterface $product): ProductInformationInterface
    {
        $preorderProductInformation = $this->preorderProductInformationFactory->create();

        if ($this->configProvider->isEnabled()) {
            $isPreorder = $this->isProductPreorder->execute($product);
            $cartLabel = $this->getCartLabel->execute($product);
            $note = $this->getNote->execute($product);
        } else {
            $isPreorder = false;
            $cartLabel = $note = '';
        }

        $preorderProductInformation->setIsPreorder($isPreorder);
        $preorderProductInformation->setCartLabel($cartLabel);
        $preorderProductInformation->setNote($note);

        return $preorderProductInformation;
    }
}
