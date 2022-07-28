<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Quote\Item;

use Amasty\Preorder\Api\Data\CartItemInformationInterface;
use Amasty\Preorder\Api\Data\CartItemInformationInterfaceFactory;
use Amasty\Preorder\Model\ConfigProvider;
use Magento\Quote\Api\Data\CartItemInterface;

class Processor
{
    /**
     * @var CartItemInformationInterfaceFactory
     */
    private $cartItemInformationFactory;

    /**
     * @var IsPreorder
     */
    private $isPreorder;

    /**
     * @var GetNote
     */
    private $getNote;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        CartItemInformationInterfaceFactory $cartItemInformationFactory,
        IsPreorder $isPreorder,
        GetNote $getNote,
        ConfigProvider $configProvider
    ) {
        $this->cartItemInformationFactory = $cartItemInformationFactory;
        $this->isPreorder = $isPreorder;
        $this->getNote = $getNote;
        $this->configProvider = $configProvider;
    }

    /**
     * @param CartItemInterface[] $quoteItems
     * @return void
     */
    public function execute(array $quoteItems): void
    {
        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getExtensionAttributes()->getPreorderInfo()) {
                continue;
            }

            $preorderCartItemInformation = $this->initCartItemPreorderInformation($quoteItem);
            $quoteItem->getExtensionAttributes()->setPreorderInfo($preorderCartItemInformation);
        }
    }

    private function initCartItemPreorderInformation(CartItemInterface $cartItem): CartItemInformationInterface
    {
        $preorderCartItemInformation = $this->cartItemInformationFactory->create();

        if ($this->configProvider->isEnabled()) {
            $isPreorder = $this->isPreorder->execute($cartItem);
            $note = $this->getNote->execute($cartItem);
        } else {
            $isPreorder = false;
            $note = '';
        }

        $preorderCartItemInformation->setIsPreorder($isPreorder);
        $preorderCartItemInformation->setNote($note);

        return $preorderCartItemInformation;
    }
}
