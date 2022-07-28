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

namespace Plumrocket\PrivateSale\Plugin\Preview;

use Magento\Catalog\Api\Data\CategoryInterface;
use Plumrocket\PrivateSale\Helper\Preview as PreviewHelper;
use Plumrocket\PrivateSale\Model\Frontend\CategoryEventPermissions;

class ManageCategoryIsActive
{
    /**
     * @var \Plumrocket\PrivateSale\Helper\Preview
     */
    private $previewHelper;

    /**
     * @var \Plumrocket\PrivateSale\Model\Frontend\CategoryEventPermissions
     */
    private $categoryEventPermissions;

    /**
     * ManageCategoryIsActive constructor.
     *
     * @param \Plumrocket\PrivateSale\Helper\Preview                          $previewHelper
     * @param \Plumrocket\PrivateSale\Model\Frontend\CategoryEventPermissions $categoryEventPermissions
     */
    public function __construct(
        PreviewHelper $previewHelper,
        CategoryEventPermissions $categoryEventPermissions
    ) {
        $this->previewHelper = $previewHelper;
        $this->categoryEventPermissions = $categoryEventPermissions;
    }

    /**
     * @param \Magento\Catalog\Api\Data\CategoryInterface $subject
     * @param                                             $result
     * @return bool
     */
    public function afterGetIsActive(CategoryInterface $subject, $result)
    {
        if ($subject->getId() && $this->previewHelper->isAllowToChangeData()) {
            return $this->categoryEventPermissions->canBrowsing((int) $subject->getId());
        }

        return $result;
    }
}
