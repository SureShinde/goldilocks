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

namespace Plumrocket\PrivateSale\Model\Integration;

use Magento\Framework\ObjectManagerInterface;

class Loader
{
    /**
     * @var array|mixed|null
     */
    private $model;

    /**
     * Loader constructor.
     * @param ObjectManagerInterface $objectManager
     * @param null $model
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $model = null
    ) {
        if (is_array($model) && isset($model['instance'])) {
            $model = $objectManager->get($model['instance']);
        }

        $this->model = $model;
    }

    /**
     * @return array|mixed|null
     */
    public function getLoadedModel()
    {
        return $this->model;
    }
}
