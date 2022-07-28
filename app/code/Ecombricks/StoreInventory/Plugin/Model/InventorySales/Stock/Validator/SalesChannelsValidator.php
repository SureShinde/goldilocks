<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventorySales\Stock\Validator;

/**
 * Sales channels validator plugin
 */
class SalesChannelsValidator
{

    /**
     * Validation result factory
     *
     * @var \Magento\Framework\Validation\ValidationResultFactory
     */
    protected $validationResultFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Validation\ValidationResultFactory $validationResultFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @return void
     */
    public function __construct(
        \Magento\Framework\Validation\ValidationResultFactory $validationResultFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->validationResultFactory = $validationResultFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * After validate
     *
     * @param \Magento\InventorySales\Model\Stock\Validator\SalesChannelsValidator $subject
     * @param \Magento\Framework\Validation\ValidationResult $result
     * @param \Magento\InventoryApi\Api\Data\StockInterface $stock
     * @return \Magento\Framework\Validation\ValidationResult
     */
    public function afterValidate(
        \Magento\InventorySales\Model\Stock\Validator\SalesChannelsValidator $subject,
        \Magento\Framework\Validation\ValidationResult $result,
        \Magento\InventoryApi\Api\Data\StockInterface $stock
    )
    {
        $salesChannels = $stock->getExtensionAttributes()->getSalesChannels();
        $errors = [];
        if (is_array($salesChannels)) {
            foreach ($salesChannels as $salesChannel) {
                $type = (string) $salesChannel->getType();
                $code = (string) $salesChannel->getCode();
                if (
                    empty($type) || empty($code) ||
                    \Ecombricks\StoreInventory\Api\InventorySalesApi\Data\SalesChannelInterface::TYPE_STORE !== $type
                ) {
                    continue;
                }
                try {
                    $this->storeManager->getStore($code);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                    $errors[] = __('The store with code "%code" does not exist.', ['code' => $code]);
                }
            }
        }
        return $this->validationResultFactory->create(['errors' => array_merge($result->getErrors(), $errors)]);
    }

}
