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

namespace Plumrocket\PrivateSale\Controller\Adminhtml\Splashpage\Image;

use Plumrocket\PrivateSale\Controller\Adminhtml\ImageUpload;

class Upload extends ImageUpload
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Plumrocket_PrivateSale::splashpage';

    /**
     * @inheritDoc
     */
    protected function getParamName()
    {
        preg_match('/^(.*?)\[.\]\[(.*?)\]$/', parent::getParamName(), $match);
        return $match[1] ?? '';
    }
}
