<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales v4.x.x
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Plugin\Product;

use Magento\Catalog\Model\Product;
use Plumrocket\PrivateSale\Model\Frontend\ProductEventPermissions;

class HideAddToCart
{
    /**
     * @var ProductEventPermissions
     */
    private $productEventPermissions;

    /**
     * HideAddToCart constructor.
     *
     * @param ProductEventPermissions $productEventPermissions
     */
    public function __construct(
        ProductEventPermissions $productEventPermissions
    ) {
        $this->productEventPermissions = $productEventPermissions;
    }

    /**
     * @param \Magento\Catalog\Model\Product $subject
     * @param $result
     * @return mixed
     */
    public function afterIsSalable(
        Product $subject,
        $result
    ) {
        return $this->productEventPermissions->canShowAddToCart((int) $subject->getId()) ? $result : false;
    }
}
