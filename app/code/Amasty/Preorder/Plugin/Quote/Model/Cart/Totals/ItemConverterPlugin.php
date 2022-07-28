<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Quote\Model\Cart\Totals;

use Amasty\Preorder\Api\Data\CartItemInformationInterfaceFactory;
use Amasty\Preorder\Model\Quote\Item\GetPreorderInformation;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Quote\Model\Cart\Totals\ItemConverter;

class ItemConverterPlugin
{
    /**
     * @var CartItemInformationInterfaceFactory
     */
    private $cartItemInformationFactory;

    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    public function __construct(
        CartItemInformationInterfaceFactory $cartItemInformationFactory,
        GetPreorderInformation $getPreorderInformation
    ) {
        $this->cartItemInformationFactory = $cartItemInformationFactory;
        $this->getPreorderInformation = $getPreorderInformation;
    }

    public function afterModelToDataObject(
        ItemConverter $subject,
        TotalsItemInterface $totalsItem,
        CartItemInterface $cartItem
    ): TotalsItemInterface {
        $this->populateExtensionAttributes($totalsItem, $cartItem);

        return $totalsItem;
    }

    private function populateExtensionAttributes(
        TotalsItemInterface $totalsItem,
        CartItemInterface $cartItem
    ): void {
        if (!$totalsItem->getExtensionAttributes()->getPreorderInfo()) {
            $cartItemPreorderInformation = $this->getPreorderInformation->execute($cartItem);
            if ($cartItemPreorderInformation->isPreorder()) {
                $preorderInformation = $this->cartItemInformationFactory->create();
                $preorderInformation->setNote($cartItemPreorderInformation->getNote());
                $totalsItem->getExtensionAttributes()->setPreorderInfo($preorderInformation);
            }

        }
    }
}
