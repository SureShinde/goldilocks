<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventorySalesAdminUi;

/**
 * Website name resolver plugin
 */
class WebsiteNameResolver
{

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @return void
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
    }

    /**
     * After resolve
     *
     * @param \Magento\InventorySalesAdminUi\Model\WebsiteNameResolver $subject
     * @param \Magento\Framework\Validation\ValidationResult $result
     * @param string $type
     * @param string $code
     * @return string
     */
    public function afterResolve(
        \Magento\InventorySalesAdminUi\Model\WebsiteNameResolver $subject,
        string $result,
        string $type,
        string $code
    ): string
    {
        return \Ecombricks\StoreInventory\Api\InventorySalesApi\Data\SalesChannelInterface::TYPE_STORE === $type ?
            $this->storeManager->getStore($code)->getName() : $result;
    }

}
