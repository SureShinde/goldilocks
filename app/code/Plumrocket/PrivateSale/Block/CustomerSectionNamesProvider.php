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
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block;

use Magento\Customer\CustomerData\SectionPool;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * @todo Use magento core class after leave compatibility with magento 2.3.3
 */
class CustomerSectionNamesProvider implements ArgumentInterface
{
    /**
     * @var SectionPool
     */
    private $sectionPool;

    /**
     * CustomerSectionNamesProvider constructor.
     *
     * @param SectionPool $sectionPool
     */
    public function __construct(SectionPool $sectionPool)
    {
        $this->sectionPool = $sectionPool;
    }

    /**
     * Return array of section names based on config.
     *
     * @return array
     */
    public function getSectionNames()
    {
        return $this->sectionPool->getSectionNames();
    }
}
