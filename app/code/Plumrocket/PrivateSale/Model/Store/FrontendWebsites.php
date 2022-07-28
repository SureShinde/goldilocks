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

namespace Plumrocket\PrivateSale\Model\Store;

use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Retrieve all websites besides admin one
 *
 * @since 5.0.0
 */
class FrontendWebsites
{
    const ADMIN_CODE = 'admin';

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var \Magento\Store\Api\Data\WebsiteInterface[]|null
     */
    private $cache;

    /**
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(WebsiteRepositoryInterface $websiteRepository)
    {
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getList(): array
    {
        if (empty($this->cache)) {
            $this->cache = [];
            foreach ($this->websiteRepository->getList() as $website) {
                if ($website->getCode() === self::ADMIN_CODE) {
                    continue;
                }
                $this->cache[] = $website;
            }
        }

        return $this->cache;
    }
}
