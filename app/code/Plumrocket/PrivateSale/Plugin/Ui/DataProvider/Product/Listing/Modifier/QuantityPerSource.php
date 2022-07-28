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
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Plugin\Ui\DataProvider\Product\Listing\Modifier;

use Magento\Framework\App\RequestInterface;

class QuantityPerSource
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param \Magento\InventoryCatalogAdminUi\Ui\DataProvider\Product\Listing\Modifier\QuantityPerSource $subject
     * @param array                                                                                       $result
     * @param array                                                                                       $meta
     * @return callable
     */
    public function afterModifyMeta(
        \Magento\InventoryCatalogAdminUi\Ui\DataProvider\Product\Listing\Modifier\QuantityPerSource $subject,
        array $result,
        array $meta
    ) {
        if ($this->request->getParam('namespace') === 'prprivatesale_event_product_listing') {
            return $meta;
        }

        return $result;
    }
}
