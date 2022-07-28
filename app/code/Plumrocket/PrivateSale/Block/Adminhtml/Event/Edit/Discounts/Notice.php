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

namespace Plumrocket\PrivateSale\Block\Adminhtml\Event\Edit\Discounts;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Plumrocket\Base\Block\Adminhtml\System\Config\Form\Version;

class Notice extends Template
{
    /**
     * @var Version
     */
    private $version;

    /**
     * Notice constructor.
     * @param Version $version
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Version $version,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getWikiLink()
    {
        return $this->version->getWikiLink();
    }
}
